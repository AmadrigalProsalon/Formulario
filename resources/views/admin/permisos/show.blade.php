@extends('admin.layout')

@section('title', 'Detalle de permiso')
@section('page_title', 'Solicitud #' . $solicitud->id)
@section('page_description', 'Seguimiento de firmas, documentos generados y recepción del formato por RH.')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.permisos.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-300">Volver</a>
        <span class="inline-flex border rounded-full px-3 py-1 text-xs {{ $solicitud->badge_class }}">{{ $solicitud->estatus_label }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold">{{ $solicitud->tipoPermiso?->nombre }}</h2>
                        <p class="text-slate-500">{{ $solicitud->empleado?->nombre }} · {{ $solicitud->empleado?->correo }}</p>
                    </div>

                    <div class="text-sm text-slate-500 md:text-right">
                        Folio interno<br>
                        <span class="font-bold text-slate-900">PER-{{ str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Área</div><div class="font-bold">{{ $solicitud->area?->nombre }}</div></div>
                    <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Líder</div><div class="font-bold">{{ $solicitud->lider?->nombre ?? 'Sin líder' }}</div></div>
                    <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Días solicitados</div><div class="font-bold">{{ $solicitud->dias_solicitados }}</div></div>
                    <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Inicio</div><div class="font-bold">{{ $solicitud->fecha_inicio?->format('d/m/Y') }}</div></div>
                    <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Fin</div><div class="font-bold">{{ $solicitud->fecha_fin?->format('d/m/Y') }}</div></div>
                    <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Recibido RH</div><div class="font-bold">{{ $solicitud->formato_recibido ? 'Sí' : 'No' }}</div></div>
                </div>

                @if($solicitud->motivo)
                    <div class="mt-5 rounded-xl bg-slate-50 p-4">
                        <div class="text-xs text-slate-500">Motivo</div>
                        {{ $solicitud->motivo }}
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-lg">Documentos</h2>
                        <p class="text-sm text-slate-500">Descarga o reenvía el formato inicial y el formato firmado a RH.</p>
                    </div>
                </div>
                <div class="p-5">
                    @include('admin.permisos._documentos_botones', ['solicitud' => $solicitud])

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5 text-sm">
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                            <div class="text-xs text-slate-500">Formato inicial enviado</div>
                            <div class="font-semibold">{{ $solicitud->documento_inicial_enviado_at?->format('d/m/Y H:i') ?? 'Pendiente / No registrado' }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                            <div class="text-xs text-slate-500">Formato firmado enviado a RH</div>
                            <div class="font-semibold">{{ $solicitud->documento_firmado_enviado_rh_at?->format('d/m/Y H:i') ?? 'Pendiente / No registrado' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-slate-200"><h2 class="font-bold text-lg">Firmas</h2></div>
                <div class="divide-y divide-slate-100">
                    @foreach($solicitud->firmas as $firma)
                        <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <div class="font-semibold">{{ $firma->nombre }}</div>
                                <div class="text-sm text-slate-500">{{ ucfirst($firma->tipo_firma) }} · {{ $firma->correo }}</div>
                            </div>
                            <div class="text-sm">
                                @if($firma->estatus === 'firmado')
                                    <span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs">Firmado {{ $firma->firmado_at?->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs">Pendiente</span>
                                @endif
                            </div>
                            <a href="{{ route('permisos.firma.show', $firma->token) }}" target="_blank" class="rounded-xl bg-slate-200 px-4 py-2 text-center hover:bg-slate-300">Abrir enlace</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="font-bold text-lg mb-4">Acciones RH</h2>
                <form method="POST" action="{{ route('admin.permisos.recibido', $solicitud) }}" class="mb-3">@csrf<button class="w-full rounded-xl bg-blue-600 text-white px-4 py-2.5 hover:bg-blue-700">Formato recibido</button></form>
                <form method="POST" action="{{ route('admin.permisos.pendiente', $solicitud) }}" class="mb-3">@csrf<button class="w-full rounded-xl bg-slate-600 text-white px-4 py-2.5 hover:bg-slate-700">Formato pendiente</button></form>
                <form method="POST" action="{{ route('admin.permisos.cancelar', $solicitud) }}" onsubmit="return confirm('¿Cancelar solicitud?')">@csrf<button class="w-full rounded-xl bg-red-600 text-white px-4 py-2.5 hover:bg-red-700">Cancelar</button></form>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="font-bold text-lg mb-4">Observaciones</h2>
                <form method="POST" action="{{ route('admin.permisos.observaciones', $solicitud) }}">
                    @csrf
                    <textarea name="observaciones_rh" rows="5" class="w-full rounded-xl border-slate-300">{{ $solicitud->observaciones_rh }}</textarea>
                    <button class="mt-3 w-full rounded-xl bg-orange-500 text-white px-4 py-2.5 hover:bg-orange-600">Guardar observaciones</button>
                </form>
            </div>
        </div>
    </div>
@endsection
