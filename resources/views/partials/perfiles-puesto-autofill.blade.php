<script>
document.addEventListener('DOMContentLoaded', function () {
    const normalizar = function (texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, '_');
    };

    const path = normalizar(window.location.pathname);
    const bodyText = normalizar(document.body.innerText);

    const esRequisicion =
        path.includes('requisicion') ||
        bodyText.includes('requisicion_de_personal') ||
        bodyText.includes('requisicion_personal');

    if (!esRequisicion) {
        return;
    }

    const form = document.querySelector('form');

    if (!form) {
        return;
    }

    const panel = document.createElement('div');
    panel.className = 'mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
    panel.innerHTML = `
        <div class="mb-4">
            <h3 class="text-lg font-bold text-slate-900">Perfil de puesto cargado</h3>
            <p class="text-sm text-slate-500">
                Selecciona un departamento y después un perfil. El sistema llenará automáticamente los campos de la requisición.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Departamento
                </label>
                <select id="autofill_departamento_perfil"
                        data-autofill-control="1"
                        class="w-full rounded-xl border-slate-300">
                    <option value="">Selecciona un departamento</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    Perfil de puesto
                </label>
                <select id="autofill_perfil_puesto"
                        data-autofill-control="1"
                        class="w-full rounded-xl border-slate-300"
                        disabled>
                    <option value="">Primero selecciona un departamento</option>
                </select>
            </div>
        </div>

        <div id="autofill_perfil_mensaje"
             class="hidden mt-4 rounded-xl p-3 text-sm">
        </div>
    `;

    form.prepend(panel);

    const departamentoSelect = document.getElementById('autofill_departamento_perfil');
    const perfilSelect = document.getElementById('autofill_perfil_puesto');
    const mensaje = document.getElementById('autofill_perfil_mensaje');

    function mostrarMensaje(texto, tipo = 'info') {
        mensaje.textContent = texto;
        mensaje.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'bg-amber-50', 'text-amber-700', 'bg-rose-50', 'text-rose-700');

        if (tipo === 'success') {
            mensaje.classList.add('bg-emerald-50', 'text-emerald-700');
        } else if (tipo === 'error') {
            mensaje.classList.add('bg-rose-50', 'text-rose-700');
        } else {
            mensaje.classList.add('bg-amber-50', 'text-amber-700');
        }
    }

    function ocultarMensaje() {
        mensaje.classList.add('hidden');
        mensaje.textContent = '';
    }

    function controlesFormulario() {
        return Array.from(document.querySelectorAll('input, select, textarea'))
            .filter(function (el) {
                return !el.dataset.autofillControl;
            });
    }

    function textoRelacionado(el) {
        let texto = [
            el.name,
            el.id,
            el.placeholder,
            el.getAttribute('aria-label')
        ].filter(Boolean).join(' ');

        if (el.id) {
            const label = document.querySelector(`label[for="${el.id}"]`);
            if (label) {
                texto += ' ' + label.innerText;
            }
        }

        const wrapper = el.closest('div, .form-group, .mb-4, .field-wrapper');
        if (wrapper) {
            const label = wrapper.querySelector('label');
            if (label) {
                texto += ' ' + label.innerText;
            }
        }

        return normalizar(texto);
    }

    function encontrarControl(keys) {
        const keysNorm = keys.map(normalizar);
        const controles = controlesFormulario();

        let candidatos = controles.map(function (el) {
            const texto = textoRelacionado(el);

            let score = 0;

            keysNorm.forEach(function (key) {
                if (texto === key) score += 100;
                if (texto.includes(key)) score += 30;
                if (normalizar(el.name).includes(key)) score += 50;
                if (normalizar(el.id).includes(key)) score += 50;
            });

            return { el, score };
        }).filter(function (item) {
            return item.score > 0;
        }).sort(function (a, b) {
            return b.score - a.score;
        });

        return candidatos.length ? candidatos[0].el : null;
    }

    function setValor(keys, valor) {
        if (valor === null || valor === undefined || valor === '') {
            return;
        }

        const control = encontrarControl(keys);

        if (!control) {
            return;
        }

        if (control.tagName === 'SELECT') {
            let existe = Array.from(control.options).some(function (option) {
                return option.value === String(valor) || option.text === String(valor);
            });

            if (!existe) {
                const option = new Option(String(valor), String(valor), true, true);
                control.add(option);
            }

            control.value = String(valor);
        } else if (control.type === 'checkbox' || control.type === 'radio') {
            control.checked = String(control.value).toLowerCase() === String(valor).toLowerCase();
        } else {
            control.value = valor;
        }

        control.dispatchEvent(new Event('input', { bubbles: true }));
        control.dispatchEvent(new Event('change', { bubbles: true }));
    }

    async function cargarDepartamentos() {
        try {
            const response = await fetch('/api/perfiles-puesto/departamentos');
            const json = await response.json();

            departamentoSelect.innerHTML = '<option value="">Selecciona un departamento</option>';

            (json.data || []).forEach(function (departamento) {
                departamentoSelect.add(new Option(departamento, departamento));
            });

            if (!json.data || json.data.length === 0) {
                mostrarMensaje('No hay departamentos cargados en perfiles de puesto.', 'info');
            }
        } catch (error) {
            mostrarMensaje('No se pudieron cargar los departamentos de perfiles de puesto.', 'error');
        }
    }

    async function cargarPerfiles(departamento) {
        perfilSelect.disabled = true;
        perfilSelect.innerHTML = '<option value="">Cargando perfiles...</option>';

        try {
            const url = '/api/perfiles-puesto?departamento=' + encodeURIComponent(departamento);
            const response = await fetch(url);
            const json = await response.json();

            perfilSelect.innerHTML = '<option value="">Selecciona un perfil</option>';

            (json.data || []).forEach(function (perfil) {
                perfilSelect.add(new Option(perfil.nombre, perfil.id));
            });

            perfilSelect.disabled = false;

            if (!json.data || json.data.length === 0) {
                perfilSelect.innerHTML = '<option value="">No hay perfiles para este departamento</option>';
                perfilSelect.disabled = true;
                mostrarMensaje('No hay perfiles cargados para este departamento.', 'info');
            }
        } catch (error) {
            perfilSelect.innerHTML = '<option value="">Error al cargar perfiles</option>';
            perfilSelect.disabled = true;
            mostrarMensaje('No se pudieron cargar los perfiles de puesto.', 'error');
        }
    }

    async function cargarPerfil(id) {
        if (!id) {
            return;
        }

        try {
            const response = await fetch('/api/perfiles-puesto/' + encodeURIComponent(id));
            const json = await response.json();
            const perfil = json.data || {};

            setValor(['departamento_solicitante', 'departamento'], perfil.departamento);
            setValor(['nombre_puesto', 'puesto_solicitado', 'puesto'], perfil.nombre_puesto);
            setValor(['area_departamento_puesto', 'area_departamento', 'departamento_puesto'], perfil.area_departamento_puesto);
            setValor(['puesto_reporta', 'reporta_a', 'reporta'], perfil.puesto_reporta);
            setValor(['funciones_generales', 'funciones', 'actividades', 'responsabilidades'], perfil.funciones_generales);
            setValor(['escolaridad', 'educacion', 'nivel_estudios'], perfil.escolaridad);
            setValor(['area_experiencia', 'experiencia_area', 'experiencia_en'], perfil.area_experiencia);
            setValor(['anios_experiencia', 'anos_experiencia', 'años_experiencia', 'tiempo_experiencia'], perfil.anios_experiencia);
            setValor(['conocimientos_indispensables', 'conocimientos_requeridos', 'conocimientos'], perfil.conocimientos_indispensables);
            setValor(['conocimientos_deseables'], perfil.conocimientos_deseables);
            setValor(['habilidades_indispensables', 'habilidades_requeridas', 'habilidades', 'competencias'], perfil.habilidades_indispensables);
            setValor(['habilidades_deseables'], perfil.habilidades_deseables);
            setValor(['software_especifico', 'software'], perfil.software_especifico);
            setValor(['hardware_requerido', 'hardware', 'equipo'], perfil.hardware_requerido);
            setValor(['nivel_ingles', 'ingles'], perfil.nivel_ingles);

            mostrarMensaje('La requisición se llenó con el perfil de puesto seleccionado.', 'success');
        } catch (error) {
            mostrarMensaje('No se pudo cargar la información del perfil seleccionado.', 'error');
        }
    }

    departamentoSelect.addEventListener('change', function () {
        ocultarMensaje();

        const departamento = departamentoSelect.value;

        perfilSelect.innerHTML = '<option value="">Primero selecciona un departamento</option>';
        perfilSelect.disabled = true;

        if (!departamento) {
            return;
        }

        setValor(['departamento_solicitante', 'departamento'], departamento);
        cargarPerfiles(departamento);
    });

    perfilSelect.addEventListener('change', function () {
        ocultarMensaje();
        cargarPerfil(perfilSelect.value);
    });

    cargarDepartamentos();
});
</script>
