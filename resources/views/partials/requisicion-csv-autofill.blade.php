@if(isset($formulario) && $formulario->slug === 'requisicion-personal')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const departamentoSelect = document.querySelector('[name="area_departamento_puesto"]')
        || document.querySelector('[name="departamento_solicitante"]');

    const perfilSelect = document.querySelector('[name="perfil_puesto_id"]');

    if (!departamentoSelect || !perfilSelect) {
        return;
    }

    perfilSelect.innerHTML = '<option value="">Primero selecciona un departamento</option>';

    departamentoSelect.addEventListener('change', function () {
        cargarPerfilesPorDepartamento(this.value, '');
    });

    perfilSelect.addEventListener('change', function () {
        if (this.value) {
            cargarPerfil(this.value);
        }
    });

    async function cargarPerfilesPorDepartamento(departamento, q) {
        if (!departamento) {
            perfilSelect.innerHTML = '<option value="">Primero selecciona un departamento</option>';
            return;
        }

        perfilSelect.innerHTML = '<option value="">Cargando perfiles...</option>';

        try {
            const url = `/api/perfiles-puesto/por-departamento?departamento=${encodeURIComponent(departamento)}&q=${encodeURIComponent(q || '')}`;
            const response = await fetch(url);
            const perfiles = await response.json();

            perfilSelect.innerHTML = '<option value="">Selecciona un perfil de puesto</option>';

            if (!perfiles.length) {
                perfilSelect.innerHTML = '<option value="">No hay perfiles para este departamento</option>';
                return;
            }

            perfiles.forEach(function (perfil) {
                const option = document.createElement('option');
                option.value = perfil.id;
                option.textContent = `${perfil.nombre_puesto}${perfil.puesto_reporta ? ' — Reporta a: ' + perfil.puesto_reporta : ''}`;
                perfilSelect.appendChild(option);
            });

            if (perfiles.length === 1) {
                perfilSelect.value = perfiles[0].id;
                cargarPerfil(perfiles[0].id);
            }
        } catch (error) {
            console.error(error);
            perfilSelect.innerHTML = '<option value="">Error al cargar perfiles</option>';
        }
    }

    async function cargarPerfil(perfilId) {
        try {
            const response = await fetch(`/api/perfiles-puesto/${perfilId}`);
            const perfil = await response.json();

            setValue('perfil_puesto_id', perfil.id);
            setValue('nombre_puesto', perfil.nombre_puesto);
            setSelectByText('nombre_puesto', perfil.nombre_puesto);
            setValue('area_departamento_puesto', perfil.area_departamento);
            setSelectByText('area_departamento_puesto', perfil.area_departamento);
            setValue('puesto_a_quien_reporta', perfil.puesto_reporta);
            setSelectByText('puesto_a_quien_reporta', perfil.puesto_reporta);

            setValue('funciones_generales_puesto', perfil.responsabilidades || perfil.descripcion_puesto);
            setValue('area_experiencia', perfil.experiencia_detectada);
            setValue('conocimientos_indispensables', perfil.requerimientos_minimos);
            setValue('conocimientos_deseables', perfil.objetivo_puesto);
            setValue('habilidades_indispensables', perfil.habilidades);
            setValue('habilidades_deseables', perfil.cualidades);
            setValue('software_especifico', perfil.software_detectado);
            setValue('hardware_especifico', perfil.hardware_detectado);

            if (perfil.ingles_detectado) {
                setSelectByText('nivel_ingles', perfil.ingles_detectado);
            }

            if (perfil.escolaridad_detectada) {
                setSelectByText('escolaridad', perfil.escolaridad_detectada);
            }
        } catch (error) {
            console.error(error);
            alert('No se pudo cargar el perfil seleccionado.');
        }
    }

    function setValue(name, value) {
        if (value === null || value === undefined || value === '') return;

        const input = document.querySelector(`[name="${name}"]`);
        if (!input) return;

        input.value = value;
        input.dispatchEvent(new Event('change'));
    }

    function setSelectByText(name, value) {
        if (!value) return;

        const select = document.querySelector(`[name="${name}"]`);
        if (!select || select.tagName.toLowerCase() !== 'select') return;

        const normalizedValue = normalize(value);

        let matched = false;
        Array.from(select.options).forEach(function (option) {
            const optionText = normalize(option.textContent);
            if (!matched && (normalizedValue.includes(optionText) || optionText.includes(normalizedValue))) {
                select.value = option.value;
                matched = true;
            }
        });

        if (!matched && value) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.appendChild(option);
            select.value = value;
        }

        select.dispatchEvent(new Event('change'));
    }

    function normalize(text) {
        return String(text || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }
});
</script>
@endif
