<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de vacaciones</title>
    @include('partials.design-assets')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="mx-auto max-w-6xl px-3 py-5 sm:px-6 md:py-9">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
        <header class="relative overflow-hidden border-b border-indigo-100 bg-gradient-to-br from-white via-indigo-50 to-cyan-50 px-6 py-8 md:px-10 md:py-11">
            <div class="absolute -right-20 -top-16 h-72 w-72 rounded-full bg-indigo-200/35 blur-3xl"></div>
            <div class="absolute bottom-0 right-24 hidden h-28 w-44 rounded-t-full bg-amber-300/70 md:block"></div>
            <div class="absolute bottom-0 right-32 hidden h-28 w-2 -rotate-3 bg-amber-800/70 md:block"></div>
            <div class="absolute -bottom-10 right-6 hidden h-32 w-64 rounded-[50%] bg-cyan-300/40 md:block"></div>
            <div class="relative max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white/80 px-3 py-1.5 text-xs font-bold uppercase tracking-[.18em] text-indigo-700 shadow-sm">
                    Recursos Humanos
                </div>
                <div class="flex items-start gap-4">
                    <div class="hidden h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-3xl text-white shadow-lg sm:flex">☀</div>
                    <div>
                        <div class="text-xl font-medium text-indigo-950">Solicitud de</div>
                        <h1 class="text-4xl font-black tracking-tight text-indigo-950 sm:text-5xl">VACACIONES</h1>
                        <p class="mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                            Selecciona al colaborador y agrega exactamente los días que desea disfrutar. Puedes elegir fechas consecutivas o salteadas.
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-4 sm:p-7 md:p-10">
            @if(session('error'))
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                    <div class="mb-2 font-bold">Revisa la información:</div>
                    <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('permisos.solicitud.store') }}" id="formPermiso" class="space-y-6">
                @csrf
                <input type="hidden" name="empleado_id" id="empleado_id" value="{{ old('empleado_id') }}">

                <section id="resumen_vacaciones" class="grid grid-cols-1 overflow-hidden rounded-3xl border border-indigo-100 bg-white opacity-50 shadow-sm transition lg:grid-cols-5">
                    <div class="lg:col-span-3 p-6 md:p-7">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-xl text-violet-700">◴</div>
                            <div>
                                <h2 class="font-black text-indigo-950">Resumen de días disponibles</h2>
                                <p class="text-xs text-slate-500">La base proviene del Excel y el proporcional se actualiza automáticamente hasta hoy.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-[150px_1fr] sm:items-center">
                            <div class="mx-auto flex h-36 w-36 flex-col items-center justify-center rounded-full border-[12px] border-emerald-100 shadow-inner">
                                <strong id="saldo_disponibles" class="text-3xl font-black text-indigo-950">0.00</strong>
                                <span class="text-xs text-slate-500">días disponibles</span>
                            </div>
                            <div class="divide-y divide-slate-100 text-sm">
                                <div class="flex justify-between gap-4 py-3"><span class="text-slate-500">Saldo año anterior</span><strong id="saldo_anterior">0.00 días</strong></div>
                                <div class="flex justify-between gap-4 py-3"><span class="text-slate-500">Saldo año actual</span><strong id="saldo_actual">0.00 días</strong></div>
                                <div class="flex justify-between gap-4 py-3"><span class="text-slate-500">Vence saldo anterior</span><strong id="saldo_vencimiento">--</strong></div>
                                <div class="flex justify-between gap-4 py-3"><span class="text-slate-500">Días usados / reservados</span><strong id="saldo_usados">0.00 días</strong></div>
                                <span id="saldo_correspondientes" class="hidden">0</span>
                                <span id="saldo_pendientes" class="hidden">0</span>
                                <div class="flex justify-between gap-4 py-3"><span class="font-bold text-indigo-950">Fechas de esta solicitud</span><strong class="text-emerald-700"><span id="resumen_dias_solicitados">0</span> día(s)</strong></div>
                                <span id="saldo_ajuste" class="hidden">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-indigo-100 bg-gradient-to-br from-violet-50 to-indigo-50 p-6 md:p-7 lg:border-l lg:border-t-0 lg:col-span-2">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-xl text-violet-700 shadow-sm">i</div>
                            <h2 class="font-black text-violet-800">Importante</h2>
                        </div>
                        <div class="space-y-4 text-sm text-indigo-950">
                            <div class="flex gap-3"><span>📅</span><p>Selecciona únicamente los días que realmente se tomarán.</p></div>
                            <div class="flex gap-3"><span>✓</span><p>Cada fecha válida equivale a un día a descontar.</p></div>
                            <div class="flex gap-3"><span>⚠</span><p>Se validan horarios, días inhábiles, festivos y fechas repetidas.</p></div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-violet-50 to-white px-5 py-4 md:px-7">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 font-black text-white shadow-md">1</div>
                        <div><h2 class="font-black text-indigo-950">Datos del colaborador</h2><p class="text-xs text-slate-500">Busca y selecciona al colaborador correcto.</p></div>
                    </div>
                    <div class="p-5 md:p-7">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-bold">Departamento</label>
                                <select id="area_filter" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                                    <option value="">Todos los departamentos</option>
                                    @foreach($areas as $area)<option value="{{ $area->id }}">{{ $area->nombre }}</option>@endforeach
                                </select>
                            </div>
                            <div class="relative md:col-span-2">
                                <label class="mb-1 block text-sm font-bold">CURP, RFC, nombre, correo o número</label>
                                <input type="text" id="empleado_search" autocomplete="off"
                                       class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                                       placeholder="Escribe al menos 2 caracteres">
                                <div id="empleado_results" class="absolute z-30 mt-2 hidden max-h-80 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl"></div>
                            </div>
                        </div>

                        <div id="empleado_card" class="mt-5 hidden rounded-3xl border border-indigo-100 bg-indigo-50/50 p-5">
                            <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                <div><h3 id="card_nombre_titulo" class="text-lg font-black text-indigo-950"></h3><p id="card_identificadores" class="text-xs text-indigo-600"></p></div>
                                <span class="self-start rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Colaborador seleccionado</span>
                            </div>
                            <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                                <div><span class="text-xs font-bold uppercase text-slate-400">Nombre</span><div id="card_nombre" class="mt-1 font-bold"></div></div>
                                <div><span class="text-xs font-bold uppercase text-slate-400">Área</span><div id="card_area" class="mt-1 font-bold"></div></div>
                                <div><span class="text-xs font-bold uppercase text-slate-400">Puesto</span><div id="card_puesto" class="mt-1 font-bold"></div></div>
                                <div><span class="text-xs font-bold uppercase text-slate-400">Ingreso</span><div id="card_fecha_ingreso" class="mt-1 font-bold"></div></div>
                                <div><span class="text-xs font-bold uppercase text-slate-400">Líder</span><div id="card_lider" class="mt-1 font-bold"></div></div>
                                <div><span class="text-xs font-bold uppercase text-slate-400">Correo líder</span><div id="card_correo_lider" class="mt-1 font-bold"></div></div>
                                <div><span class="text-xs font-bold uppercase text-slate-400">Correo colaborador</span><div id="card_correo" class="mt-1 font-bold"></div></div>
                                <div><span class="text-xs font-bold uppercase text-slate-400">Horario</span><div id="card_horario" class="mt-1 font-bold"></div></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-violet-50 to-white px-5 py-4 md:px-7">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 font-black text-white shadow-md">2</div>
                        <div><h2 class="font-black text-indigo-950">Información de las vacaciones</h2><p class="text-xs text-slate-500">Puedes agregar fechas consecutivas o completamente salteadas.</p></div>
                    </div>
                    <div class="p-5 md:p-7">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-bold">Tipo de permiso</label>
                                <select name="tipo_permiso_id" id="tipo_permiso_id" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500" required>
                                    <option value="">Selecciona una opción</option>
                                    @foreach($tiposPermisos as $tipo)
                                        <option value="{{ $tipo->id }}" data-vacaciones="{{ ($tipo->slug === 'vacaciones' || $tipo->descuenta_vacaciones) ? '1' : '0' }}" @selected(old('tipo_permiso_id') == $tipo->id)>{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-bold">Total de días solicitados</label>
                                <div class="relative">
                                    <input type="number" step="0.5" min="0.5" name="dias_solicitados" id="dias_solicitados" value="{{ old('dias_solicitados') }}" class="w-full rounded-xl border-slate-300 bg-slate-50 pr-16 text-lg font-black focus:border-violet-500 focus:ring-violet-500" required>
                                    <span class="absolute right-4 top-3 text-sm font-bold text-slate-400">días</span>
                                </div>
                                <p id="dias_help" class="hidden mt-1 text-xs text-violet-600">Se calcula automáticamente con las fechas agregadas.</p>
                            </div>
                        </div>

                        <div id="rango_fechas_group" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div><label class="mb-1 block text-sm font-bold">Fecha inicio</label><input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full rounded-xl border-slate-300" required></div>
                            <div><label class="mb-1 block text-sm font-bold">Fecha fin</label><input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-xl border-slate-300" required></div>
                        </div>

                        <div id="vacaciones_fechas_group" class="mt-5 hidden">
                            <div class="rounded-2xl border border-violet-100 bg-violet-50/60 p-4">
                                <div class="mb-3 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                                    <div><div class="font-black text-indigo-950">Agregar día de vacaciones</div><div class="text-xs text-slate-500">Repite este paso por cada fecha. No tienen que ser consecutivas.</div></div>
                                    <div class="rounded-xl bg-violet-600 px-4 py-2 text-center text-white"><span id="contador_fechas" class="text-xl font-black">0</span><span class="ml-1 text-xs">día(s)</span></div>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto]">
                                    <input type="date" id="fecha_vacacion_input" class="w-full rounded-xl border-violet-200 bg-white focus:border-violet-500 focus:ring-violet-500">
                                    <button type="button" id="agregar_fecha_vacacion" class="rounded-xl bg-violet-600 px-5 py-3 font-bold text-white shadow-md hover:bg-violet-700">＋ Agregar día</button>
                                </div>
                                <div id="mensaje_fechas" class="hidden"></div>
                            </div>

                            <div class="mt-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-4">
                                <div class="mb-3 font-bold text-slate-700">Días que se descontarán</div>
                                <div id="fechas_vacaciones_lista" class="flex min-h-12 flex-wrap gap-2"></div>
                                <div id="fechas_hidden"></div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-1 block text-sm font-bold">Motivo o comentarios adicionales <span class="font-normal text-slate-400">(opcional)</span></label>
                            <textarea name="motivo" rows="3" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500" placeholder="Agrega alguna indicación necesaria">{{ old('motivo') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-cyan-50">
                    <div class="flex flex-col items-center justify-between gap-5 p-5 md:flex-row md:p-6">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-xl shadow-sm">💡</div>
                            <div><div class="font-black text-emerald-800">Recuerda</div><p class="mt-1 text-sm text-emerald-700">Verifica el saldo y las fechas seleccionadas. El formato será enviado al colaborador, líder y RH.</p></div>
                        </div>
                        <button class="w-full rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-8 py-3.5 font-black text-white shadow-lg shadow-violet-600/20 hover:from-violet-700 hover:to-indigo-700 md:w-auto">
                            ✈ Enviar solicitud
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </div>
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
const contadorFechas = document.getElementById('contador_fechas');
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
    document.getElementById('resumen_vacaciones').classList.remove('opacity-50');
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
    document.getElementById('saldo_correspondientes').textContent = Number(emp.saldo?.dias_ganados_hoy ?? emp.saldo?.saldo_excel ?? 0).toFixed(2);
    document.getElementById('saldo_anterior').textContent = Number(emp.saldo?.saldo_anio_anterior ?? 0).toFixed(2) + ' días';
    document.getElementById('saldo_actual').textContent = Number(emp.saldo?.saldo_anio_actual ?? 0).toFixed(2) + ' días';
    document.getElementById('saldo_vencimiento').textContent = emp.saldo?.fecha_vencimiento ?? '--';
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
                <div class="text-xs text-slate-500">Líder: ${emp.lider ?? 'Sin líder'} · Disponible: ${emp.saldo?.dias_disponibles ?? 0} días · Horario: ${emp.calendario_laboral?.descripcion ?? 'L-V'}</div>
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
        if (contadorFechas) contadorFechas.textContent = fechas.length;
        const resumenSolicitados = document.getElementById('resumen_dias_solicitados');
        if (resumenSolicitados) resumenSolicitados.textContent = fechas.length;
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
        agregarFechaBtn.textContent = 'Añadir fecha';
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
