document.addEventListener('DOMContentLoaded', () => {
    const hiddenInput = document.querySelector('input[name="perfil_puesto_id"]');

    if (!hiddenInput) {
        return;
    }

    hiddenInput.type = 'hidden';

    const wrapper = document.createElement('div');
    wrapper.className = 'space-y-2';

    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Buscar perfil por puesto, área o puesto al que reporta...';
    searchInput.className = 'w-full rounded-xl border-slate-300';
    searchInput.autocomplete = 'off';

    const resultsBox = document.createElement('div');
    resultsBox.className = 'hidden rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden';

    const help = document.createElement('p');
    help.className = 'text-xs text-slate-500';
    help.textContent = 'Selecciona un perfil para autollenar datos de la requisición. Puedes ajustar los campos antes de enviar.';

    hiddenInput.parentNode.insertBefore(wrapper, hiddenInput);
    wrapper.appendChild(searchInput);
    wrapper.appendChild(resultsBox);
    wrapper.appendChild(help);
    wrapper.appendChild(hiddenInput);

    let timer = null;

    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        const q = searchInput.value.trim();

        if (q.length < 2) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }

        timer = setTimeout(async () => {
            const response = await fetch(`/api/perfiles-puesto/buscar?q=${encodeURIComponent(q)}`);
            const results = await response.json();

            renderResults(results);
        }, 250);
    });

    function renderResults(results) {
        resultsBox.innerHTML = '';

        if (!Array.isArray(results) || results.length === 0) {
            resultsBox.innerHTML = '<div class="p-3 text-sm text-slate-500">No se encontraron perfiles.</div>';
            resultsBox.classList.remove('hidden');
            return;
        }

        results.forEach((perfil) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left p-3 hover:bg-slate-50 border-b border-slate-100 last:border-b-0';
            button.innerHTML = `
                <div class="font-semibold text-sm">${escapeHtml(perfil.nombre_puesto || '')}</div>
                <div class="text-xs text-slate-500">${escapeHtml(perfil.area_departamento || 'Sin área')} · Reporta a: ${escapeHtml(perfil.puesto_reporta || 'Sin dato')}</div>
            `;

            button.addEventListener('click', async () => {
                hiddenInput.value = perfil.id;
                searchInput.value = perfil.nombre_puesto || '';
                resultsBox.classList.add('hidden');
                await cargarPerfil(perfil.id);
            });

            resultsBox.appendChild(button);
        });

        resultsBox.classList.remove('hidden');
    }

    async function cargarPerfil(id) {
        const response = await fetch(`/api/perfiles-puesto/${id}`);
        const perfil = await response.json();

        setField('nombre_puesto', perfil.nombre_puesto);
        setField('area_departamento_puesto', perfil.area_departamento);
        setField('puesto_a_quien_reporta', perfil.puesto_reporta);
        setField('funciones_generales_puesto', perfil.responsabilidades_text || perfil.objetivo_puesto || perfil.descripcion_puesto);
        setField('area_experiencia', perfil.descripcion_puesto);
        setField('conocimientos_indispensables', perfil.requerimientos_minimos);
        setField('conocimientos_deseables', perfil.cualidades);
        setField('habilidades_indispensables', perfil.habilidades);
        setField('habilidades_deseables', perfil.cualidades);
        setField('software_especifico', perfil.requerimientos_minimos);
        setField('nivel_ingles', perfil.nivel_ingles);
        setField('anios_experiencia', perfil.anios_experiencia);

        mostrarAviso('Perfil cargado. Revisa y ajusta los datos antes de enviar.');
    }

    function setField(name, value) {
        if (!value) return;

        const field = document.querySelector(`[name="${name}"]`);
        const radios = document.querySelectorAll(`input[type="radio"][name="${name}"]`);

        if (radios.length) {
            const radio = Array.from(radios).find((r) => normalizar(r.value) === normalizar(value));
            if (radio) radio.checked = true;
            return;
        }

        if (!field) return;

        if (field.tagName === 'SELECT') {
            const option = Array.from(field.options).find((opt) => normalizar(opt.value) === normalizar(value) || normalizar(opt.textContent) === normalizar(value));

            if (option) {
                field.value = option.value;
            } else {
                const newOption = new Option(value, value, true, true);
                field.add(newOption);
                field.value = value;
            }
            return;
        }

        field.value = value;
    }

    function mostrarAviso(message) {
        let alert = document.getElementById('perfil-puesto-alert');
        if (!alert) {
            alert = document.createElement('div');
            alert.id = 'perfil-puesto-alert';
            alert.className = 'rounded-xl bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 text-sm';
            wrapper.appendChild(alert);
        }
        alert.textContent = message;
    }

    function normalizar(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
