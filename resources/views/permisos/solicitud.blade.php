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
                        Captura la solicitud. El sistema generará el formato y lo enviará al líder, colaborador y RH.
                        La firma será física y RH marcará cuando reciba el documento.
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
                        <p class="text-sm text-slate-500">Busca por nombre, correo o número de empleado. No se cargan los 200 empleados de golpe.</p>
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
                        <label class="block text-sm font-semibold mb-1">Colaborador</label>
                        <input type="text" id="empleado_search" autocomplete="off" placeholder="Escribe al menos 2 letras..." class="w-full rounded-xl border-slate-300">
                        <div id="empleado_results" class="hidden absolute z-30 bg-white border border-slate-200 rounded-2xl shadow-lg mt-2 w-full max-h-80 overflow-y-auto"></div>
                    </div>
                </div>

                <div id="empleado_card" class="hidden mt-5 rounded-2xl bg-blue-50 border border-blue-200 p-5 text-blue-950">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="font-semibold">Nombre:</span><br><span id="card_nombre"></span></div>
                        <div><span class="font-semibold">Área:</span><br><span id="card_area"></span></div>
                        <div><span class="font-semibold">Puesto:</span><br><span id="card_puesto"></span></div>
                        <div><span class="font-semibold">Líder:</span><br><span id="card_lider"></span></div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Vacaciones asignadas</span><br><strong id="saldo_correspondientes">0</strong></div>
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Usadas / recibidas RH</span><br><strong id="saldo_usados">0</strong></div>
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Pendientes formato</span><br><strong id="saldo_pendientes">0</strong></div>
                        <div class="rounded-xl bg-white p-3"><span class="text-slate-500">Disponibles</span><br><strong id="saldo_disponibles">0</strong></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-9 h-9 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold">2</span>
                    <div>
                        <h2 class="text-xl font-bold">Datos del permiso</h2>
                        <p class="text-sm text-slate-500">Para vacaciones se valida saldo, pero no se descuenta hasta que RH marque formato recibido.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tipo de permiso</label>
                        <select name="tipo_permiso_id" class="w-full rounded-xl border-slate-300" required>
                            <option value="">Selecciona una opción</option>
                            @foreach($tiposPermisos as $tipo)
                                <option value="{{ $tipo->id }}" @selected(old('tipo_permiso_id') == $tipo->id)>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Días solicitados</label>
                        <input type="number" step="0.5" min="0.5" name="dias_solicitados" value="{{ old('dias_solicitados') }}" class="w-full rounded-xl border-slate-300" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full rounded-xl border-slate-300" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Fecha fin</label>
                        <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-xl border-slate-300" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">Motivo / comentarios</label>
                        <textarea name="motivo" rows="4" class="w-full rounded-xl border-slate-300">{{ old('motivo') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="text-sm text-slate-500">
                    Al enviar se generará el DOCX y se mandará por correo. La firma digital está desactivada por ahora.
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
let timer = null;

function hideResults() {
    results.classList.add('hidden');
    results.innerHTML = '';
}

function renderEmpleado(emp) {
    empleadoId.value = emp.id;
    input.value = `${emp.nombre} — ${emp.area ?? 'Sin área'}`;
    hideResults();

    document.getElementById('empleado_card').classList.remove('hidden');
    document.getElementById('card_nombre').textContent = emp.nombre ?? '';
    document.getElementById('card_area').textContent = emp.area ?? 'Sin área';
    document.getElementById('card_puesto').textContent = emp.puesto ?? 'Sin puesto';
    document.getElementById('card_lider').textContent = emp.lider ?? 'Sin líder asignado';
    document.getElementById('saldo_correspondientes').textContent = emp.saldo?.dias_correspondientes ?? 0;
    document.getElementById('saldo_usados').textContent = emp.saldo?.dias_usados ?? 0;
    document.getElementById('saldo_pendientes').textContent = emp.saldo?.dias_pendientes_formato ?? 0;
    document.getElementById('saldo_disponibles').textContent = emp.saldo?.dias_disponibles ?? 0;
}

async function buscar() {
    const q = input.value.trim();
    empleadoId.value = '';

    if (q.length < 2) {
        hideResults();
        return;
    }

    const url = new URL('{{ route('permisos.empleados.buscar') }}', window.location.origin);
    url.searchParams.set('q', q);
    if (area.value) url.searchParams.set('area_id', area.value);

    const res = await fetch(url.toString());
    const data = await res.json();

    results.innerHTML = '';

    if (!data.length) {
        results.innerHTML = '<div class="p-4 text-sm text-slate-500">Sin resultados</div>';
        results.classList.remove('hidden');
        return;
    }

    data.forEach(emp => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'w-full text-left p-4 hover:bg-slate-50 border-b border-slate-100';
        item.innerHTML = `
            <div class="font-semibold text-slate-900">${emp.nombre}</div>
            <div class="text-xs text-slate-500">${emp.numero_empleado ?? ''} · ${emp.area ?? 'Sin área'} · ${emp.correo ?? ''}</div>
            <div class="text-xs text-blue-700 mt-1">Líder: ${emp.lider ?? 'Sin líder'}</div>
        `;
        item.addEventListener('click', () => renderEmpleado(emp));
        results.appendChild(item);
    });

    results.classList.remove('hidden');
}

input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(buscar, 300);
});

area.addEventListener('change', () => {
    if (input.value.trim().length >= 2) buscar();
});

document.addEventListener('click', (e) => {
    if (!results.contains(e.target) && e.target !== input) hideResults();
});
</script>
</body>
</html>
