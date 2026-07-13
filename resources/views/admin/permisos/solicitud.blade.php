<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de permiso / ausencia</title>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="rounded-3xl bg-slate-950 text-white p-8 mb-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <p class="text-slate-300 text-sm uppercase tracking-wide">Recursos Humanos</p>
                    <h1 class="text-3xl font-bold mt-2">Solicitud de permiso / ausencia</h1>
                    <p class="text-slate-300 mt-2 max-w-2xl">
                        Busca al colaborador por CURP, RFC, nombre, correo o número de empleado. El sistema cargará sus datos, líder, saldo de vacaciones y calendario laboral.
                    </p>
                </div>

                @if(auth()->check() && auth()->user()->is_admin)
                    <a href="{{ route('admin.permisos.index') }}" class="rounded-xl bg-white text-slate-950 px-5 py-3 font-semibold hover:bg-slate-100">
                        Panel RH
                    </a>
                @endif
            </div>
        </div>

        @if(session('error'))
            <div class="mb-5 rounded-2xl bg-red-100 border border-red-300 text-red-800 px-5 py-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-2xl bg-red-100 border border-red-300 text-red-800 px-5 py-4">
                <div class="font-semibold mb-2">Revisa estos campos:</div>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('permisos.solicitud.store') }}" class="space-y-6" id="formPermiso">
            @csrf
            <input type="hidden" name="empleado_id" id="empleado_id" value="{{ old('empleado_id') }}">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-9 h-9 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold">1</span>
                    <div>
                        <h2 class="text-xl font-bold">Buscar colaborador</h2>
                        <p class="text-sm text-slate-500">Busca por CURP, RFC, nombre, correo o número de empleado. También puedes filtrar por departamento.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Filtrar por departamento</label>
                        <select id="area_filter" class="w-full rounded-xl border-slate-300">
                            <option value="">Todos</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 relative">
                        <label class="block text-sm font-semibold mb-1">CURP, RFC o nombre del colaborador</label>
                        <input type="text" id="empleado_search" autocomplete="off" placeholder="Ej. CURP, RFC, Juan Pérez, correo o número empleado" class="w-full rounded-xl border-slate-300 uppercase md:normal-case">
                        <div id="empleado_results" class="hidden absolute z-30 bg-white border border-slate-200 rounded-2xl shadow-lg mt-2 w-full max-h-80 overflow-y-auto"></div>
                    </div>
                </div>

                <div id="empleado_card" class="hidden mt-5 rounded-2xl bg-blue-50 border border-blue-200 p-5 text-blue-950">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="font-bold text-lg" id="card_nombre_titulo"></h3>
                            <p class="text-sm text-blue-800" id="card_identificadores"></p>
                        </div>
                        <span class="rounded-full bg-blue-100 text-blue-800 px-3 py-1 text-xs font-semibold">Colaborador seleccionado</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="font-semibold">Nombre:</span><br><span id="card_nombre"></span></div>
                        <div><span class="font-semibold">Área:</span><br><span id="card_area"></span></div>
                        <div><span class="font-semibold">Puesto:</span><br><span id="card_puesto"></span></div>
                        <div><span class="font-semibold">Fecha ingreso:</span><br><span id="card_fecha_ingreso"></span></div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="font-semibold">Jefe / líder:</span><br><span id="card_lider"></span></div>
                        <div><span class="font-semibold">Correo líder:</span><br><span id="card_correo_lider"></span></div>
                        <div><span class="font-semibold">Correo colaborador:</span><br><span id="card_correo"></span></div>
                        <div><span class="font-semibold">Horario laboral:</span><br><span id="card_horario"></span></div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-4 text-sm">
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Saldo oficial Excel</span><br><strong id="saldo_correspondientes">0</strong></div>
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Días por ley (referencia)</span><br><strong id="saldo_ajuste">0</strong></div>
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Usadas / recibidas RH</span><br><strong id="saldo_usados">0</strong></div>
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Pendientes formato</span><br><strong id="saldo_pendientes">0</strong></div>
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Restantes</span><br><strong id="saldo_disponibles">0</strong></div>
                    </div>

                    <p class="mt-3 text-xs text-blue-800">
                        Nota: las vacaciones se descuentan únicamente cuando RH marca el formato como recibido.
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-9 h-9 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold">2</span>
                    <div>
                        <h2 class="text-xl font-bold">Datos del permiso</h2>
                        <p class="text-sm text-slate-500">En vacaciones se validan saldo, horario laboral y días inhábiles. Puedes seleccionar fechas salteadas.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tipo de permiso</label>
                        <select name="tipo_permiso_id" id="tipo_permiso_id" class="w-full rounded-xl border-slate-300" required>
                            <option value="">Selecciona una opción</option>
                            @foreach($tiposPermisos as $tipo)
                                <option
                                    value="{{ $tipo->id }}"
                                    data-vacaciones="{{ ($tipo->slug === 'vacaciones' || $tipo->descuenta_vacaciones) ? '1' : '0' }}"
                                    @selected(old('tipo_permiso_id') == $tipo->id)
                                >
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Días solicitados</label>
                        <input type="number" step="0.5" min="0.5" name="dias_solicitados" id="dias_solicitados" value="{{ old('dias_solicitados') }}" class="w-full rounded-xl border-slate-300" required>
                        <p id="dias_help" class="hidden text-xs text-slate-500 mt-1">En vacaciones se calcula automáticamente con las fechas válidas seleccionadas.</p>
                    </div>
                </div>

                <div id="rango_fechas_group" class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full rounded-xl border-slate-300" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Fecha fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-xl border-slate-300" required>
                    </div>
                </div>

                <div id="vacaciones_fechas_group" class="hidden mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-col md:flex-row md:items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold mb-1">Agregar día de vacaciones</label>
                            <input type="date" id="fecha_vacacion_input" class="w-full rounded-xl border-slate-300">
                        </div>
                        <button type="button" id="agregar_fecha_vacacion" class="rounded-xl bg-slate-950 text-white px-5 py-3 font-semibold hover:bg-slate-800">
                            Agregar día
                        </button>
                    </div>

                    <div class="mt-3 text-sm text-slate-600">
                        Puedes agregar días continuos o salteados. El sistema no permitirá días fuera del horario laboral del colaborador ni días festivos/inhábiles registrados.
                    </div>

                    <div id="mensaje_fechas" class="hidden mt-3 rounded-xl px-4 py-3 text-sm"></div>

                    <div class="mt-4">
                        <div class="text-sm font-semibold mb-2">Días seleccionados</div>
                        <div id="fechas_vacaciones_lista" class="flex flex-wrap gap-2"></div>
                        <div id="fechas_hidden"></div>
                    </div>
                </div>

                <div class="mt-5">
                    <label class="block text-sm font-semibold mb-1">Motivo / comentarios</label>
                    <textarea name="motivo" rows="4" class="w-full rounded-xl border-slate-300">{{ old('motivo') }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="text-sm text-slate-500">
                    Al enviar se generará el DOCX y se mandará por correo al colaborador, líder y RH.
                    Correo RH configurado: <strong>rhformularios@prosalon.mx</strong>.
                </div>
                <button class="rounded-xl bg-slate-950 text-white px-8 py-3 hover:bg-slate-800 font-semibold">
                    Generar y enviar formato
                </button>
            </div>
        </form>
    </div>

<script>
const input = document.getElementById('empleado_search');
const results = document.getElementById('empleado_results');
const area = document.getElementById('area_filter');
const empleadoId = document.getElementById('empleado_id');
const form = document.getElementById('formPermiso');
const tipoPermiso = document.getElementById('tipo_permiso_id');
const diasSolicitados = document.getElementById('dias_solicitados');
const diasHelp = document.getElementById('dias_help');
const rangoGroup = document.getElementById('rango_fechas_group');
const fechaInicio = document.getElementById('fecha_inicio');
const fechaFin = document.getElementById('fecha_fin');
const vacacionesGroup = document.getElementById('vacaciones_fechas_group');
const fechaVacacionInput = document.getElementById('fecha_vacacion_input');
const agregarFechaBtn = document.getElementById('agregar_fecha_vacacion');
const fechasLista = document.getElementById('fechas_vacaciones_lista');
const fechasHidden = document.getElementById('fechas_hidden');
const mensajeFechas = document.getElementById('mensaje_fechas');
const csrfToken = document.querySelector('input[name="_token"]').value;
const urlValidarFechas = @json(route('permisos.fechas.validar'));

let timer = null;
let empleadoSeleccionado = null;
let fechasVacaciones = new Set(@json(old('fechas_seleccionadas', [])));
let validandoSubmit = false;

function esVacaciones() {
    const option = tipoPermiso.options[tipoPermiso.selectedIndex];
    return option?.dataset?.vacaciones === '1';
}

function hideResults() {
    results.classList.add('hidden');
    results.innerHTML = '';
}

function mostrarMensajeFechas(texto, tipo = 'info') {
    mensajeFechas.textContent = texto;
    mensajeFechas.className = 'mt-3 rounded-xl px-4 py-3 text-sm';

    if (tipo === 'error') {
        mensajeFechas.classList.add('bg-red-100', 'border', 'border-red-300', 'text-red-800');
    } else if (tipo === 'success') {
        mensajeFechas.classList.add('bg-emerald-100', 'border', 'border-emerald-300', 'text-emerald-800');
    } else {
        mensajeFechas.classList.add('bg-blue-100', 'border', 'border-blue-300', 'text-blue-800');
    }

    mensajeFechas.classList.remove('hidden');
}

function ocultarMensajeFechas() {
    mensajeFechas.classList.add('hidden');
}

function renderEmpleado(emp) {
    const empleadoAnteriorId = empleadoId.value;
    empleadoSeleccionado = emp;
    empleadoId.value = emp.id;
    input.value = `${emp.nombre} — ${emp.curp || emp.rfc || emp.numero_empleado || 'Sin clave'}`;
    hideResults();

    document.getElementById('empleado_card').classList.remove('hidden');
    document.getElementById('card_nombre_titulo').textContent = emp.nombre ?? '';
    document.getElementById('card_identificadores').textContent = `CURP: ${emp.curp || 'Sin CURP'} · RFC: ${emp.rfc || 'Sin RFC'} · No. empleado: ${emp.numero_empleado || 'Sin número'}`;
    document.getElementById('card_nombre').textContent = emp.nombre ?? '';
    document.getElementById('card_area').textContent = emp.area ?? 'Sin área';
    document.getElementById('card_puesto').textContent = emp.puesto ?? 'Sin puesto';
    document.getElementById('card_fecha_ingreso').textContent = emp.fecha_ingreso_formato ?? 'Sin fecha';
    document.getElementById('card_lider').textContent = emp.lider ?? 'Sin líder asignado';
    document.getElementById('card_correo_lider').textContent = emp.correo_lider ?? 'Sin correo';
    document.getElementById('card_correo').textContent = emp.correo ?? 'Sin correo';
    document.getElementById('card_horario').textContent = emp.calendario_laboral?.descripcion ?? 'Lunes a viernes';
    document.getElementById('saldo_correspondientes').textContent = Number(emp.saldo?.saldo_excel ?? 0).toFixed(2);
    document.getElementById('saldo_ajuste').textContent = Number(emp.saldo?.dias_correspondientes ?? 0).toFixed(2);
    document.getElementById('saldo_usados').textContent = Number(emp.saldo?.dias_usados ?? 0).toFixed(2);
    document.getElementById('saldo_pendientes').textContent = Number(emp.saldo?.dias_pendientes_formato ?? 0).toFixed(2);
    document.getElementById('saldo_disponibles').textContent = Number(emp.saldo?.dias_disponibles ?? emp.saldo?.dias_restantes ?? 0).toFixed(2);

    if (empleadoAnteriorId && String(empleadoAnteriorId) !== String(emp.id)) {
        fechasVacaciones.clear();
    }
    renderFechasVacaciones();
    ocultarMensajeFechas();
}

async function buscar() {
    const q = input.value.trim();
    empleadoId.value = '';
    empleadoSeleccionado = null;

    if (q.length < 2) {
        hideResults();
        return;
    }

    const params = new URLSearchParams({ q });
    if (area.value) params.append('area_id', area.value);

    results.innerHTML = '<div class="p-4 text-slate-500">Buscando...</div>';
    results.classList.remove('hidden');

    try {
        const response = await fetch(`{{ route('permisos.empleados.buscar') }}?${params.toString()}`);
        const empleados = await response.json();

        if (!empleados.length) {
            results.innerHTML = '<div class="p-4 text-slate-500">No se encontraron empleados.</div>';
            return;
        }

        results.innerHTML = '';
        empleados.forEach(emp => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'w-full text-left p-4 hover:bg-slate-50 border-b border-slate-100 last:border-b-0';
            item.innerHTML = `
                <div class="font-semibold text-slate-900">${emp.nombre ?? ''}</div>
                <div class="text-xs text-slate-500">CURP: ${emp.curp ?? 'Sin CURP'} · RFC: ${emp.rfc ?? 'Sin RFC'} · No: ${emp.numero_empleado ?? 'Sin número'}</div>
                <div class="text-xs text-slate-500">${emp.area ?? 'Sin área'} · ${emp.puesto ?? 'Sin puesto'} · Ingreso: ${emp.fecha_ingreso_formato ?? 'Sin fecha'}</div>
                <div class="text-xs text-slate-500">Líder: ${emp.lider ?? 'Sin líder'} · Restantes: ${emp.saldo?.dias_disponibles ?? 0} días · Horario: ${emp.calendario_laboral?.descripcion ?? 'L-V'}</div>
            `;
            item.addEventListener('click', () => renderEmpleado(emp));
            results.appendChild(item);
        });
    } catch (e) {
        results.innerHTML = '<div class="p-4 text-red-600">Error al buscar empleados.</div>';
    }
}

function actualizarModoFechas() {
    if (esVacaciones()) {
        rangoGroup.classList.add('hidden');
        fechaInicio.required = false;
        fechaFin.required = false;
        fechaInicio.disabled = true;
        fechaFin.disabled = true;
        vacacionesGroup.classList.remove('hidden');
        diasSolicitados.readOnly = true;
        diasSolicitados.step = '1';
        diasSolicitados.min = '1';
        diasHelp.classList.remove('hidden');
        renderFechasVacaciones();
    } else {
        rangoGroup.classList.remove('hidden');
        fechaInicio.required = true;
        fechaFin.required = true;
        fechaInicio.disabled = false;
        fechaFin.disabled = false;
        vacacionesGroup.classList.add('hidden');
        diasSolicitados.readOnly = false;
        diasSolicitados.step = '0.5';
        diasSolicitados.min = '0.5';
        diasHelp.classList.add('hidden');
    }
}

function renderFechasVacaciones() {
    const fechas = Array.from(fechasVacaciones).sort();
    fechasLista.innerHTML = '';
    fechasHidden.innerHTML = '';

    fechas.forEach(fecha => {
        const wrapper = document.createElement('span');
        wrapper.className = 'inline-flex items-center gap-2 rounded-full bg-slate-950 text-white px-3 py-2 text-sm';

        const texto = document.createElement('span');
        texto.textContent = new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX');

        const quitar = document.createElement('button');
        quitar.type = 'button';
        quitar.className = 'font-bold text-slate-300 hover:text-white';
        quitar.textContent = '×';
        quitar.addEventListener('click', () => {
            fechasVacaciones.delete(fecha);
            renderFechasVacaciones();
        });

        wrapper.append(texto, quitar);
        fechasLista.appendChild(wrapper);

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'fechas_seleccionadas[]';
        hidden.value = fecha;
        fechasHidden.appendChild(hidden);
    });

    if (!fechas.length) {
        fechasLista.innerHTML = '<span class="text-sm text-slate-400">Aún no has seleccionado días.</span>';
    }

    if (esVacaciones()) {
        diasSolicitados.value = fechas.length || '';
    }
}

async function validarFechasRemoto(fechas) {
    if (!empleadoId.value) {
        throw new Error('Selecciona primero un colaborador.');
    }

    const response = await fetch(urlValidarFechas, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            empleado_id: empleadoId.value,
            fechas,
        }),
    });

    if (!response.ok) {
        const payload = await response.json().catch(() => ({}));
        throw new Error(payload.message || 'No se pudieron validar las fechas.');
    }

    return response.json();
}

agregarFechaBtn.addEventListener('click', async function () {
    ocultarMensajeFechas();

    if (!empleadoId.value) {
        mostrarMensajeFechas('Selecciona primero un colaborador.', 'error');
        return;
    }

    const fecha = fechaVacacionInput.value;
    if (!fecha) {
        mostrarMensajeFechas('Selecciona una fecha antes de agregarla.', 'error');
        return;
    }

    if (fechasVacaciones.has(fecha)) {
        mostrarMensajeFechas('Esa fecha ya está seleccionada.', 'error');
        return;
    }

    try {
        agregarFechaBtn.disabled = true;
        agregarFechaBtn.textContent = 'Validando...';

        const resultado = await validarFechasRemoto([fecha]);

        if (resultado.invalidas?.length) {
            mostrarMensajeFechas(resultado.invalidas[0].fecha_formato + ': ' + resultado.invalidas[0].motivo, 'error');
            return;
        }

        fechasVacaciones.add(fecha);
        fechaVacacionInput.value = '';
        renderFechasVacaciones();
        mostrarMensajeFechas('Día agregado correctamente.', 'success');
    } catch (e) {
        mostrarMensajeFechas(e.message || 'No se pudo validar la fecha.', 'error');
    } finally {
        agregarFechaBtn.disabled = false;
        agregarFechaBtn.textContent = 'Agregar día';
    }
});

input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(buscar, 300);
});

area.addEventListener('change', function () {
    if (input.value.trim().length >= 2) buscar();
});

tipoPermiso.addEventListener('change', actualizarModoFechas);

document.addEventListener('click', function (e) {
    if (!results.contains(e.target) && e.target !== input) {
        hideResults();
    }
});

form.addEventListener('submit', async function (e) {
    if (validandoSubmit) {
        return;
    }

    if (!empleadoId.value) {
        e.preventDefault();
        alert('Selecciona un colaborador desde la lista de resultados antes de enviar.');
        return;
    }

    if (!esVacaciones()) {
        return;
    }

    e.preventDefault();

    const fechas = Array.from(fechasVacaciones).sort();
    if (!fechas.length) {
        mostrarMensajeFechas('Selecciona al menos un día de vacaciones.', 'error');
        return;
    }

    if (Number(diasSolicitados.value) !== fechas.length) {
        mostrarMensajeFechas(`Los días solicitados (${diasSolicitados.value || 0}) no coinciden con las ${fechas.length} fechas seleccionadas.`, 'error');
        return;
    }

    try {
        const resultado = await validarFechasRemoto(fechas);
        if (resultado.invalidas?.length) {
            const detalle = resultado.invalidas.map(item => `${item.fecha_formato}: ${item.motivo}`).join(' | ');
            mostrarMensajeFechas(detalle, 'error');
            return;
        }

        validandoSubmit = true;
        form.submit();
    } catch (e) {
        mostrarMensajeFechas(e.message || 'No se pudieron validar las fechas.', 'error');
    }
});

actualizarModoFechas();
renderFechasVacaciones();
</script>
</body>
</html>
