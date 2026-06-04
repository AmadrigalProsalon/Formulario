<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de permiso o ausencia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-slate-800">
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="bg-white/95 backdrop-blur rounded-3xl shadow-xl border border-white/20 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-700 to-slate-950 text-white p-8">
                <div class="inline-flex rounded-full bg-white/10 px-3 py-1 text-sm mb-4">RH · Permisos y ausencias</div>
                <h1 class="text-3xl md:text-4xl font-bold">Solicitud de permiso</h1>
                <p class="text-blue-100 mt-2">Vacaciones, permisos con goce, permisos sin goce y otros permisos internos.</p>
            </div>

            <div class="p-6 md:p-8">
                @if(session('error'))<div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                <form method="POST" action="{{ route('permisos.store') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Colaborador *</label>
                            <select name="empleado_id" id="empleado_id" class="w-full rounded-xl border-slate-300" required>
                                <option value="">Selecciona colaborador</option>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id }}" data-disponibles="{{ $empleado->vacaciones_disponibles }}" data-lider="{{ $empleado->lider?->nombre ?? 'Sin líder asignado' }}" data-area="{{ $empleado->area?->nombre ?? 'Sin área' }}" @selected(old('empleado_id') == $empleado->id)>
                                        {{ $empleado->nombre }} — {{ $empleado->correo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Tipo de permiso *</label>
                            <select name="tipo_permiso_id" id="tipo_permiso_id" class="w-full rounded-xl border-slate-300" required>
                                <option value="">Selecciona tipo</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id }}" data-requiere-saldo="{{ $tipo->requiere_saldo ? 1 : 0 }}" data-descuenta="{{ $tipo->descuenta_vacaciones ? 1 : 0 }}" @selected(old('tipo_permiso_id') == $tipo->id)>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Fecha inicio *</label>
                            <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full rounded-xl border-slate-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Fecha fin *</label>
                            <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-xl border-slate-300" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-1">Motivo / comentarios</label>
                            <textarea name="motivo" rows="4" class="w-full rounded-xl border-slate-300" placeholder="Agrega detalles del permiso solicitado">{{ old('motivo') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div><div class="text-xs text-slate-500">Área</div><div id="resumen_area" class="font-bold">Selecciona colaborador</div></div>
                        <div><div class="text-xs text-slate-500">Líder que recibirá firma</div><div id="resumen_lider" class="font-bold">Selecciona colaborador</div></div>
                        <div><div class="text-xs text-slate-500">Vacaciones disponibles</div><div id="resumen_disponibles" class="font-bold">0 días</div></div>
                    </div>

                    <div id="alerta_saldo" class="hidden rounded-xl bg-blue-50 border border-blue-200 text-blue-800 p-4 text-sm">
                        Este tipo de permiso valida saldo de vacaciones. No permitirá solicitar más días de los disponibles.
                    </div>

                    <div class="flex justify-end">
                        <button class="rounded-2xl bg-slate-950 text-white px-8 py-3 font-semibold hover:bg-slate-800 shadow-lg">
                            Enviar solicitud y mandar a firma
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const empleadoSelect = document.getElementById('empleado_id');
        const tipoSelect = document.getElementById('tipo_permiso_id');
        const area = document.getElementById('resumen_area');
        const lider = document.getElementById('resumen_lider');
        const disponibles = document.getElementById('resumen_disponibles');
        const alertaSaldo = document.getElementById('alerta_saldo');

        function refreshResumen() {
            const empleado = empleadoSelect.options[empleadoSelect.selectedIndex];
            area.textContent = empleado?.dataset?.area || 'Selecciona colaborador';
            lider.textContent = empleado?.dataset?.lider || 'Selecciona colaborador';
            disponibles.textContent = (empleado?.dataset?.disponibles || 0) + ' días';

            const tipo = tipoSelect.options[tipoSelect.selectedIndex];
            alertaSaldo.classList.toggle('hidden', !(tipo?.dataset?.requiereSaldo === '1'));
        }

        empleadoSelect.addEventListener('change', refreshResumen);
        tipoSelect.addEventListener('change', refreshResumen);
        refreshResumen();
    </script>
</body>
</html>
