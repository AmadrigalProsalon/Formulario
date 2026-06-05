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

    const existingDepartmentField = document.querySelector('[name="area_departamento_puesto"]')
        || document.querySelector('[name="departamento_solicitante"]')
        || document.querySelector('[name="departamento"]');

    const panel = document.createElement('div');
    panel.className = 'bg-white rounded-2xl shadow-sm border border-blue-200 p-5 mb-6';
    panel.innerHTML = `
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Perfil de puesto base</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Primero selecciona el departamento. Después el sistema mostrará solo los perfiles importados de esa área.
                </p>
            </div>
            <span class="rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-semibold w-fit">Autollenado</span>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Departamento para filtrar perfiles</label>
                <select id="perfil-departamento-filter" class="w-full rounded-xl border-slate-300">
                    <option value="">Selecciona un departamento</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">Si ya seleccionaste departamento en el formulario, se usará automáticamente.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Perfil / puesto disponible</label>
                <select id="perfil-puesto-select" class="w-full rounded-xl border-slate-300" disabled>
                    <option value="">Primero selecciona un departamento</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">Al elegir el perfil, se llenarán los campos detectados desde el Word.</p>
            </div>
        </div>

        <div class="mt-4 relative">
            <label class="block text-sm font-semibold mb-1">Búsqueda dentro del departamento</label>
            <input id="perfil-puesto-search" type="text" autocomplete="off" placeholder="Escribe para filtrar el puesto seleccionado..." class="w-full rounded-xl border-slate-300" disabled>
            <div id="perfil-puesto-results" class="hidden absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-80 overflow-y-auto"></div>
        </div>

        <div id="perfil-puesto-selected" class="hidden mt-4 rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm"></div>
    `;

    form.parentNode.insertBefore(panel, form);

    const departmentFilter = panel.querySelector('#perfil-departamento-filter');
    const perfilSelect = panel.querySelector('#perfil-puesto-select');
    const search = panel.querySelector('#perfil-puesto-search');
    const results = panel.querySelector('#perfil-puesto-results');
    const selectedBox = panel.querySelector('#perfil-puesto-selected');
    let currentProfiles = [];
    let timer = null;

    loadAreas().then(() => {
        const initialDepartment = getExistingDepartmentValue();
        if (initialDepartment) {
            setDepartmentFilter(initialDepartment);
            loadProfilesByDepartment(initialDepartment);
        }
    });

    if (existingDepartmentField) {
        existingDepartmentField.addEventListener('change', function () {
            const department = this.value;
            setDepartmentFilter(department);
            loadProfilesByDepartment(department);
        });
    }

    departmentFilter.addEventListener('change', function () {
        syncExistingDepartment(this.value);
        loadProfilesByDepartment(this.value);
    });

    perfilSelect.addEventListener('change', function () {
        if (this.value) {
            loadProfileDetail(this.value);
        }
    });

    search.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        const department = departmentFilter.value;

        if (!department) {
            results.classList.add('hidden');
            results.innerHTML = '';
            return;
        }

        if (q.length < 2) {
            renderSearchResults(currentProfiles);
            return;
        }

        timer = setTimeout(async () => {
            const response = await fetch(`/api/perfiles-puesto/buscar?departamento=${encodeURIComponent(department)}&q=${encodeURIComponent(q)}`);
            const items = await response.json();
            renderSearchResults(items);
        }, 250);
    });

    results.addEventListener('click', function (event) {
        const button = event.target.closest('.perfil-result');
        if (!button) return;

        perfilSelect.value = button.dataset.id;
        results.classList.add('hidden');
        loadProfileDetail(button.dataset.id);
    });

    document.addEventListener('click', function (event) {
        if (!panel.contains(event.target)) {
            results.classList.add('hidden');
        }
    });

    async function loadAreas() {
        try {
            const response = await fetch('/api/perfiles-puesto/areas');
            const areas = await response.json();
            areas.forEach(area => addOptionIfMissing(departmentFilter, area, area));
        } catch (error) {
            console.error('Error cargando áreas:', error);
        }
    }

    async function loadProfilesByDepartment(department) {
        resetProfileSelection();

        if (!department) {
            perfilSelect.innerHTML = '<option value="">Primero selecciona un departamento</option>';
            perfilSelect.disabled = true;
            search.disabled = true;
            return;
        }

        perfilSelect.disabled = true;
        search.disabled = true;
        perfilSelect.innerHTML = '<option value="">Cargando perfiles...</option>';

        try {
            const response = await fetch(`/api/perfiles-puesto/por-departamento?departamento=${encodeURIComponent(department)}`);
            currentProfiles = await response.json();

            perfilSelect.innerHTML = '<option value="">Selecciona un perfil de puesto</option>';

            if (!currentProfiles.length) {
                perfilSelect.innerHTML = '<option value="">No hay perfiles importados para este departamento</option>';
                return;
            }

            currentProfiles.forEach(perfil => {
                const label = `${perfil.nombre_puesto || 'Sin nombre'} — ${perfil.puesto_reporta || 'Sin puesto al que reporta'}`;
                addOptionIfMissing(perfilSelect, perfil.id, label);
            });

            perfilSelect.disabled = false;
            search.disabled = false;
            search.placeholder = 'Filtrar perfiles de ' + department + '...';
        } catch (error) {
            console.error('Error cargando perfiles:', error);
            perfilSelect.innerHTML = '<option value="">Error al cargar perfiles</option>';
        }
    }

    async function loadProfileDetail(id) {
        try {
            const response = await fetch(`/api/perfiles-puesto/${id}`);
            const perfil = await response.json();

            document.querySelector('input[name="perfil_puesto_id"]').value = perfil.id;

            selectedBox.classList.remove('hidden');
            selectedBox.innerHTML = `
                <div class="font-semibold text-slate-900">Perfil seleccionado: ${escapeHtml(perfil.nombre_puesto || '')}</div>
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
            fillField('conocimientos_deseables', perfil.objetivo_puesto);
            fillField('habilidades_indispensables', perfil.habilidades);
            fillField('habilidades_deseables', perfil.cualidades);
            fillField('software_especifico', perfil.software_detectado || perfil.requerimientos_minimos);
            fillField('nivel_ingles', mapIngles(perfil.ingles_detectado));
        } catch (error) {
            console.error('Error cargando detalle de perfil:', error);
            alert('No se pudo cargar el perfil seleccionado.');
        }
    }

    function renderSearchResults(items) {
        if (!items.length) {
            results.innerHTML = '<div class="p-4 text-sm text-slate-500">Sin resultados para este departamento.</div>';
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
    }

    function resetProfileSelection() {
        currentProfiles = [];
        document.querySelector('input[name="perfil_puesto_id"]').value = '';
        selectedBox.classList.add('hidden');
        selectedBox.innerHTML = '';
        search.value = '';
        results.classList.add('hidden');
        results.innerHTML = '';
    }

    function getExistingDepartmentValue() {
        return existingDepartmentField ? existingDepartmentField.value : '';
    }

    function setDepartmentFilter(value) {
        if (!value) return;
        addOptionIfMissing(departmentFilter, value, value);
        departmentFilter.value = value;
    }

    function syncExistingDepartment(value) {
        if (!existingDepartmentField || !value) return;

        if (existingDepartmentField.tagName === 'SELECT') {
            addOptionIfMissing(existingDepartmentField, value, value);
        }

        existingDepartmentField.value = value;
        existingDepartmentField.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function addOptionIfMissing(select, value, label) {
        if (!select || value === null || value === undefined || value === '') return;
        const exists = Array.from(select.options).some(option => String(option.value) === String(value));
        if (!exists) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            select.appendChild(option);
        }
    }

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

    function mapIngles(value) {
        const v = normalize(value);
        if (!v) return '';
        if (v.includes('avanzado')) return 'Avanzado';
        if (v.includes('intermedio')) return 'Intermedio';
        if (v.includes('basico')) return 'Básico';
        if (v.includes('ninguno')) return 'Ninguno';
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
