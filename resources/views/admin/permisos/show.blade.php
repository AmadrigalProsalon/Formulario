@extends('admin.layout')

@section('title', 'Detalle permiso')
@section('page_title', 'Solicitud #' . $permiso->id)
@section('page_description', 'Control de formato físico y recepción RH.')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.permisos.index') }}" class="rounded-xl bg-slate-200 px-4 py-2 hover:bg-slate-300">Volver</a>
        <a href="{{ route('admin.permisos.descargar', $permiso) }}" class="rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Descargar documento</a>
        @if($permiso->archivo_firmado_path)
            <a href="{{ route('admin.permisos.formato_firmado.descargar', $permiso) }}" class="rounded-xl bg-green-600 text-white px-4 py-2 hover:bg-green-700">Descargar firmado</a>
        @endif
        <form method="POST" action="{{ route('admin.permisos.reenviar', $permiso) }}">@csrf<button class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Reenviar documento</button></form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold mb-4">Información de la solicitud</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Colaborador</span><div class="font-semibold">{{ $permiso->empleado?->nombre }}</div></div>
                <div><span class="text-slate-500">Correo</span><div class="font-semibold">{{ $permiso->empleado?->correo }}</div></div>
                <div><span class="text-slate-500">Departamento</span><div class="font-semibold">{{ $permiso->area?->nombre ?? $permiso->empleado?->area?->nombre }}</div></div>
                <div><span class="text-slate-500">Líder</span><div class="font-semibold">{{ $permiso->lider?->nombre ?? $permiso->empleado?->lider?->nombre }}</div></div>
                <div><span class="text-slate-500">Tipo</span><div class="font-semibold">{{ $permiso->tipoPermiso?->nombre }}</div></div>
                <div><span class="text-slate-500">Días</span><div class="font-semibold">{{ $permiso->dias_solicitados }}</div></div>
                @if($permiso->diasSeleccionados->isNotEmpty())
                    <div class="md:col-span-2">
                        <span class="text-slate-500">Días seleccionados</span>
                        <div class="font-semibold">
                            {{ $permiso->diasSeleccionados->map(fn ($dia) => $dia->fecha->format('d/m/Y'))->implode(', ') }}
                        </div>
                    </div>
                @else
                    <div><span class="text-slate-500">Fecha inicio</span><div class="font-semibold">{{ $permiso->fecha_inicio?->format('d/m/Y') }}</div></div>
                    <div><span class="text-slate-500">Fecha fin</span><div class="font-semibold">{{ $permiso->fecha_fin?->format('d/m/Y') }}</div></div>
                @endif
                <div><span class="text-slate-500">Estatus</span><div class="font-semibold">{{ str_replace('_', ' ', $permiso->estatus) }}</div></div>
                <div><span class="text-slate-500">Documento enviado</span><div class="font-semibold">{{ $permiso->documento_enviado_at?->format('d/m/Y H:i') ?? 'No enviado' }}</div></div>
                <div class="md:col-span-2"><span class="text-slate-500">Motivo</span><div class="font-semibold">{{ $permiso->motivo ?: 'Sin motivo' }}</div></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold mb-4">Acciones RH</h2>
            <p class="text-sm text-slate-500 mb-4">Los días solo descuentan cuando se marca formato recibido. Cancelado no descuenta.</p>

            <form method="POST" action="{{ route('admin.permisos.recibido', $permiso) }}" class="mb-3">
                @csrf
                <textarea name="observaciones_rh" rows="2" placeholder="Observaciones opcionales" class="w-full rounded-xl border-slate-300 mb-2">{{ $permiso->observaciones_rh }}</textarea>
                <button class="w-full rounded-xl bg-green-600 text-white px-4 py-2 hover:bg-green-700">Formato recibido</button>
            </form>

            <form method="POST" action="{{ route('admin.permisos.pendiente', $permiso) }}" class="mb-3">
                @csrf
                <button class="w-full rounded-xl bg-yellow-500 text-white px-4 py-2 hover:bg-yellow-600">Formato pendiente</button>
            </form>

            <form method="POST" action="{{ route('admin.permisos.observaciones', $permiso) }}" class="mb-3">
                @csrf
                <textarea name="observaciones_rh" rows="2" required placeholder="Escribe la observación" class="w-full rounded-xl border-slate-300 mb-2">{{ $permiso->observaciones_rh }}</textarea>
                <button class="w-full rounded-xl bg-orange-500 text-white px-4 py-2 hover:bg-orange-600">Con observaciones</button>
            </form>

            <form method="POST" action="{{ route('admin.permisos.cancelar', $permiso) }}" onsubmit="return confirm('¿Cancelar solicitud? No descontará días.')">
                @csrf
                <textarea name="observaciones_rh" rows="2" placeholder="Motivo de cancelación opcional" class="w-full rounded-xl border-slate-300 mb-2">{{ $permiso->observaciones_rh }}</textarea>
                <button class="w-full rounded-xl bg-red-600 text-white px-4 py-2 hover:bg-red-700">Cancelar solicitud</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold mb-4">Formato firmado escaneado</h2>
            @if($permiso->archivo_firmado_path)
                <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 p-4 mb-4 text-sm">
                    <div><strong>Archivo:</strong> {{ $permiso->archivo_firmado_original }}</div>
                    <div><strong>Subido:</strong> {{ $permiso->archivo_firmado_at?->format('d/m/Y H:i') }}</div>
                    <div><strong>Por:</strong> {{ $permiso->archivoFirmadoPor?->name ?? 'Sistema' }}</div>
                </div>
                <a href="{{ route('admin.permisos.formato_firmado.descargar', $permiso) }}" class="inline-flex rounded-xl bg-green-600 text-white px-4 py-2 hover:bg-green-700">Descargar formato firmado</a>
            @else
                <p class="text-sm text-slate-500 mb-4">Aún no se ha subido el documento firmado físicamente.</p>
            @endif

            <form method="POST" action="{{ route('admin.permisos.formato_firmado.subir', $permiso) }}" enctype="multipart/form-data" class="mt-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Subir archivo firmado</label>
                    <input type="file" name="archivo_firmado" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full rounded-xl border border-slate-300 p-3" required>
                </div>
                <button class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Guardar formato firmado</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold mb-4">Historial</h2>
            <div class="space-y-3">
                @forelse($permiso->historial as $item)
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="font-semibold">{{ str_replace('_', ' ', ucfirst($item->accion)) }}</div>
                        <div class="text-sm text-slate-500">{{ $item->created_at?->format('d/m/Y H:i') }} · {{ $item->usuario?->name ?? 'Sistema' }}</div>
                        @if($item->descripcion)
                            <div class="text-sm mt-1">{{ $item->descripcion }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-sm text-slate-500">Sin historial registrado.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mt-6">
        <h2 class="text-xl font-bold mb-4">Historial de envíos</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50"><tr><th class="text-left p-3">Correo</th><th class="text-left p-3">Tipo</th><th class="text-left p-3">Estatus</th><th class="text-left p-3">Fecha</th><th class="text-left p-3">Error</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($envios as $envio)
                        <tr><td class="p-3">{{ $envio->correo }}</td><td class="p-3">{{ $envio->tipo }}</td><td class="p-3">{{ $envio->estatus }}</td><td class="p-3">{{ $envio->enviado_at }}</td><td class="p-3">{{ $envio->error }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="p-5 text-center text-slate-500">Sin envíos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
