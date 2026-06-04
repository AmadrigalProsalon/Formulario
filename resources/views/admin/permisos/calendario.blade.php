@extends('admin.layout')

@section('title', 'Calendario de ausencias')
@section('page_title', 'Calendario de ausencias')
@section('page_description', 'Visualiza vacaciones, permisos y ausencias por mes.')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.permisos.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-300">Listado</a>
        <a href="{{ route('permisos.solicitud.create') }}" target="_blank" class="rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Nueva solicitud</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.permisos.calendario') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Mes</label>
                <select name="mes" class="w-full rounded-xl border-slate-300">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" @selected($mes == $i)>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Año</label>
                <input type="number" name="anio" value="{{ $anio }}" class="w-full rounded-xl border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Departamento</label>
                <select name="area_id" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(($filters['area_id'] ?? '') == $area->id)>{{ $area->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tipo</label>
                <select name="tipo_permiso_id" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    @foreach($tiposPermisos as $tipo)
                        <option value="{{ $tipo->id }}" @selected(($filters['tipo_permiso_id'] ?? '') == $tipo->id)>{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Estatus</label>
                <select name="estatus" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    @foreach(['formato_generado','formato_enviado','formato_pendiente','formato_recibido','con_observaciones','cancelado'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filters['estatus'] ?? '') === $estatus)>{{ str_replace('_', ' ', ucfirst($estatus)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-xl font-bold mb-4">{{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}</h2>

        <div class="space-y-3">
            @forelse($solicitudes as $solicitud)
                @php
                    $estatusClass = match($solicitud->estatus) {
                        'formato_recibido' => 'bg-green-100 text-green-700',
                        'con_observaciones' => 'bg-orange-100 text-orange-700',
                        'cancelado' => 'bg-red-100 text-red-700',
                        default => 'bg-yellow-100 text-yellow-700',
                    };
                @endphp
                <a href="{{ route('admin.permisos.show', $solicitud) }}" class="block rounded-2xl border border-slate-200 p-4 hover:bg-slate-50">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <div class="font-bold">{{ $solicitud->empleado?->nombre }}</div>
                            <div class="text-sm text-slate-500">
                                {{ $solicitud->tipoPermiso?->nombre }} · {{ $solicitud->area?->nombre ?? $solicitud->empleado?->area?->nombre }}
                            </div>
                        </div>
                        <div class="text-sm md:text-right">
                            <div class="font-semibold">{{ $solicitud->fecha_inicio?->format('d/m/Y') }} - {{ $solicitud->fecha_fin?->format('d/m/Y') }}</div>
                            <span class="inline-flex mt-1 rounded-full px-3 py-1 text-xs {{ $estatusClass }}">{{ str_replace('_', ' ', $solicitud->estatus) }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-8 text-center text-slate-500">
                    No hay ausencias en este periodo.
                </div>
            @endforelse
        </div>
    </div>
@endsection
