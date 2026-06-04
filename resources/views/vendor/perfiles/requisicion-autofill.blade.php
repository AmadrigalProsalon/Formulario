@if(isset($formulario) && ($formulario->slug ?? '') === 'requisicion-personal')
<script>
(function () {
    const form = document.querySelector('form');
    if (!form) return;

    if (!document.querySelector('input[name="perfil_puesto_id"]')) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'perfil_puesto_id';
        form.appendChild(hidden);
    }

    const panel = document.createElement('div');
    panel.className = 'bg-white rounded-2xl shadow-sm border border-blue-200 p-5 mb-6';
    panel.innerHTML = `
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Perfil de puesto base</h2>
                <p class="text-sm text-slate-500 mt-1">Busca un perfil importado desde Word para autollenar la requisición.</p>
            </div>
            <span class="rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-semibold">Autollenado</span>
        </div>
        <div class="mt-4 relative">
            <input id="perfil-puesto-search" type="text" autocomplete="off" placeholder="Buscar por puesto, área o puesto al que reporta..." class="w-full rounded-xl border-slate-300">
            <div id="perfil-puesto-results" class="hidden absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-80 overflow-y-auto"></div>
        </div>
        <div id="perfil-puesto-selected" class="hidden mt-4 rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm"></div>
    `;

    form.parentNode.insertBefore(panel, form);

    const search = panel.querySelector('#perfil-puesto-search');
    const results = panel.querySelector('#perfil-puesto-results');
    const selectedBox = panel.querySelector('#perfil-puesto-selected');
    let timer = null;

    search.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();

        if (q.length < 2) {
            results.classList.add('hidden');
            results.innerHTML = '';
            return;
        }

        timer = setTimeout(async () => {
            const response = await fetch(`/api/perfiles-puesto/buscar?q=${encodeURIComponent(q)}`);
            const items = await response.json();

            if (!items.length) {
                results.innerHTML = '<div class="p-4 text-sm text-slate-500">Sin resultados.</div>';
                results.classList.remove('hidden');
                return;
            }

            results.innerHTML = items.map(item => `
                <button type="button" data-id="${item.id}" class="perfil-result w-full text-left p-4 hover:bg-slate-50 border-b border-slate-100 last:border-b-0">
                    <div class="font-semibold text-slate-900">${escapeHtml(item.nombre_puesto || '')}</div>
                    <div class="text-xs text-slate-500">Área: ${escapeHtml(item.area_departamento || 'Sin dato')} · Reporta a: ${escapeHtml(item.puesto_reporta || 'Sin dato')}</div>
                </button>
            `).join('');
            results.classList.remove('hidden');
        }, 250);
    });

    results.addEventListener('click', async function (event) {
        const button = event.target.closest('.perfil-result');
        if (!button) return;

        const id = button.dataset.id;
        const response = await fetch(`/api/perfiles-puesto/${id}`);
        const perfil = await response.json();

        document.querySelector('input[name="perfil_puesto_id"]').value = perfil.id;
        search.value = perfil.nombre_puesto || '';
        results.classList.add('hidden');

        selectedBox.classList.remove('hidden');
        selectedBox.innerHTML = `
            <div class="font-semibold">Perfil seleccionado: ${escapeHtml(perfil.nombre_puesto || '')}</div>
            <div class="text-slate-500">Área: ${escapeHtml(perfil.area_departamento || 'Sin dato')} · Reporta a: ${escapeHtml(perfil.puesto_reporta || 'Sin dato')}</div>
            <div class="mt-2 text-green-700 font-semibold">Se autollenaron los campos detectados. Puedes editarlos antes de enviar.</div>
        `;

        fillField('nombre_puesto', perfil.nombre_puesto);
        fillField('area_departamento_puesto', perfil.area_departamento);
        fillField('puesto_a_quien_reporta', perfil.puesto_reporta);
        fillField('funciones_generales_puesto', joinBlocks(perfil.descripcion_puesto, perfil.objetivo_puesto, perfil.responsabilidades));
        fillField('escolaridad', mapEscolaridad(perfil.escolaridad_detectada));
        fillField('area_experiencia', perfil.experiencia_detectada);
        fillField('conocimientos_indispensables', perfil.requerimientos_minimos);
        fillField('conocimientos_deseables', perfil.cualidades);
        fillField('habilidades_indispensables', perfil.habilidades);
        fillField('habilidades_deseables', perfil.cualidades);
        fillField('software_especifico', perfil.software_detectado || perfil.requerimientos_minimos);
        fillField('nivel_ingles', perfil.ingles_detectado);
    });

    document.addEventListener('click', function (event) {
        if (!panel.contains(event.target)) {
            results.classList.add('hidden');
        }
    });

    function fillField(name, value) {
        if (!value) return;

        const fields = document.querySelectorAll(`[name="${name}"], [name="${name}[]"]`);
        if (!fields.length) return;

        const first = fields[0];

        if (first.tagName === 'SELECT') {
            let matched = false;
            Array.from(first.options).forEach(option => {
                if (normalize(option.value) === normalize(value) || normalize(option.textContent) === normalize(value)) {
                    first.value = option.value;
                    matched = true;
                }
            });
            if (!matched) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                first.appendChild(option);
                first.value = value;
            }
            first.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }

        if (first.type === 'radio') {
            fields.forEach(field => {
                field.checked = normalize(field.value) === normalize(value);
            });
            return;
        }

        if (first.type === 'checkbox') {
            const values = String(value).split(/,|\n/).map(v => normalize(v)).filter(Boolean);
            fields.forEach(field => {
                field.checked = values.includes(normalize(field.value));
            });
            return;
        }

        first.value = value;
        first.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function joinBlocks(...blocks) {
        return blocks.filter(Boolean).join('\n\n');
    }

    function normalize(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();
    }

    function mapEscolaridad(value) {
        const v = normalize(value);
        if (!v) return '';
        if (v.includes('universitaria') || v.includes('licenciatura')) return 'Licenciatura';
        if (v.includes('tecnica') || v.includes('titulacion tecnica')) return 'Carrera técnica';
        if (v.includes('ingenieria')) return 'Ingeniería';
        if (v.includes('preparatoria') || v.includes('bachillerato')) return 'Preparatoria / Bachillerato';
        return value;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
</script>
@endif
