<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de vacaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Solicitud de vacaciones</h1>
                    <p class="text-slate-500 mt-2">Consulta tus días disponibles y registra tu solicitud.</p>
                </div>

                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="flex gap-2 flex-wrap">
                        <a href="{{ route('admin.vacaciones.index') }}" class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Solicitudes</a>
                        <a href="{{ route('admin.vacaciones.empleados.index') }}" class="rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Empleados</a>
                    </div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-xl bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                <div class="font-semibold mb-2">Revisa estos campos:</div>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Consultar saldo</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Número de empleado o correo</label>
                    <input type="text" id="identificador_consulta" class="w-full rounded-xl border-slate-300" placeholder="Ej. 1001 o correo@empresa.com">
                </div>

                <div class="flex items-end">
                    <button type="button" id="btnConsultar" class="w-full rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">
                        Consultar
                    </button>
                </div>
            </div>

            <div id="resultadoConsulta" class="hidden mt-5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 p-4"></div>
        </div>

        <form method="POST" action="{{ route('vacaciones.store') }}" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @csrf

            <div class="p-5 border-b border-slate-200 bg-slate-50">
                <h2 class="text-xl font-bold">Registrar solicitud</h2>
                <p class="text-sm text-slate-500">El sistema no permitirá solicitar más días de los disponibles.</p>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Número de empleado o correo <span class="text-red-600">*</span></label>
                    <input type="text" name="identificador" id="identificador_form" value="{{ old('identificador') }}" class="w-full rounded-xl border-slate-300" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Fecha de inicio <span class="text-red-600">*</span></label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full rounded-xl border-slate-300" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Fecha de fin <span class="text-red-600">*</span></label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-xl border-slate-300" required>
                </div>

                <div class="md:col-span-2">
                    <div id="diasCalculados" class="hidden rounded-xl bg-slate-100 border border-slate-200 p-4 text-sm"></div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Comentarios</label>
                    <textarea name="comentarios_empleado" rows="4" class="w-full rounded-xl border-slate-300">{{ old('comentarios_empleado') }}</textarea>
                </div>
            </div>

            <div class="p-5 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="submit" class="rounded-xl bg-green-600 text-white px-8 py-3 hover:bg-green-700">
                    Enviar solicitud
                </button>
            </div>
        </form>
    </div>

    <script>
        const token = '{{ csrf_token() }}';
        const consultaUrl = '{{ route('vacaciones.consultar-empleado') }}';
        const calcularUrl = '{{ route('vacaciones.calcular-dias') }}';

        async function postJson(url, data) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const json = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(json.message || 'Ocurrió un error.');
            }

            return json;
        }

        document.getElementById('btnConsultar').addEventListener('click', async () => {
            const identificador = document.getElementById('identificador_consulta').value.trim();
            const contenedor = document.getElementById('resultadoConsulta');

            if (!identificador) {
                alert('Ingresa número de empleado o correo.');
                return;
            }

            try {
                const data = await postJson(consultaUrl, { identificador });
                document.getElementById('identificador_form').value = identificador;

                contenedor.classList.remove('hidden');
                contenedor.innerHTML = `
                    <div class="font-bold text-lg">${data.empleado.nombre}</div>
                    <div class="text-sm mb-3">${data.empleado.departamento || ''} ${data.empleado.puesto ? ' - ' + data.empleado.puesto : ''}</div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div><strong>Totales:</strong><br>${data.saldo.dias_totales}</div>
                        <div><strong>Usados:</strong><br>${data.saldo.dias_usados}</div>
                        <div><strong>Pendientes:</strong><br>${data.saldo.dias_pendientes}</div>
                        <div><strong>Disponibles:</strong><br><span class="text-2xl font-bold">${data.saldo.dias_disponibles}</span></div>
                    </div>
                `;
            } catch (error) {
                contenedor.classList.remove('hidden');
                contenedor.className = 'mt-5 rounded-xl bg-red-100 border border-red-300 text-red-800 p-4';
                contenedor.innerHTML = error.message;
            }
        });

        async function calcularDias() {
            const fecha_inicio = document.getElementById('fecha_inicio').value;
            const fecha_fin = document.getElementById('fecha_fin').value;
            const contenedor = document.getElementById('diasCalculados');

            if (!fecha_inicio || !fecha_fin) return;

            try {
                const data = await postJson(calcularUrl, { fecha_inicio, fecha_fin });
                contenedor.classList.remove('hidden');
                contenedor.innerHTML = `<strong>Días laborables a descontar:</strong> ${data.dias}`;
            } catch (error) {
                contenedor.classList.remove('hidden');
                contenedor.innerHTML = `<span class="text-red-600">${error.message}</span>`;
            }
        }

        document.getElementById('fecha_inicio').addEventListener('change', calcularDias);
        document.getElementById('fecha_fin').addEventListener('change', calcularDias);
    </script>
</body>
</html>
