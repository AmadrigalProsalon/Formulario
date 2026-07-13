@extends('admin.layout')

@section('title', 'Permisos y ausencias')
@section('page_title', 'Permisos y ausencias')
@section('page_description', 'Control de formatos físicos, recepción RH y solicitudes por colaborador.')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total</div>
            <div class="text-3xl font-bold">{{ $solicitudes->total() }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Recibidos</div>
            <div class="text-3xl font-bold text-green-700">{{ \App\Models\PermisoSolicitud::where('estatus','formato_recibido')->count() }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Pendientes</div>
            <div class="text-3xl font-bold text-yellow-700">{{ \App\Models\PermisoSolicitud::whereIn('estatus',['formato_generado','formato_enviado','formato_pendiente'])->count() }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Cancelados</div>
            <div class="text-3xl font-bold text-red-700">{{ \App\Models\PermisoSolicitud::where('estatus','cancelado')->count() }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.permisos.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Buscar colaborador</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-xl border-slate-300" placeholder="Nombre, correo o número">
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
                    @foreach(['formato_enviado','formato_pendiente','formato_recibido','con_observaciones','cancelado'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filters['estatus'] ?? '') === $estatus)>{{ ucfirst(str_replace('_',' ', $estatus)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">Filtrar</button>
                <a href="{{ route('admin.permisos.index') }}" class="rounded-xl bg-slate-200 px-4 py-2.5 hover:bg-slate-300">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Folio</th>
                        <th class="text-left p-4">Colaborador</th>
                        <th class="text-left p-4">Departamento</th>
                        <th class="text-left p-4">Tipo</th>
                        <th class="text-left p-4">Fechas</th>
                        <th class="text-left p-4">Estatus</th>
                        <th class="text-right p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($solicitudes as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-bold">#{{ $s->id }}</td>
                            <td class="p-4">
                                <div class="font-semibold">{{ $s->empleado?->nombre }}</div>
                                <div class="text-xs text-slate-500">{{ $s->empleado?->correo }}</div>
                                <div class="text-xs text-blue-700">Líder: {{ $s->lider?->nombre ?? 'Sin líder' }}</div>
                            </td>
                            <td class="p-4">{{ $s->area?->nombre ?? $s->empleado?->area?->nombre }}</td>
                            <td class="p-4">{{ $s->tipoPermiso?->nombre }}</td>
                            <td class="p-4">
                                {{ $s->fecha_inicio?->format('d/m/Y') }} al {{ $s->fecha_fin?->format('d/m/Y') }}
                                <div class="text-xs text-slate-500">{{ $s->dias_solicitados }} días</div>
                            </td>
                            <td class="p-4">
                                @php
                                    $classes = [
                                        'formato_recibido' => 'bg-green-100 text-green-700',
                                        'formato_pendiente' => 'bg-yellow-100 text-yellow-700',
                                        'formato_enviado' => 'bg-blue-100 text-blue-700',
                                        'con_observaciones' => 'bg-orange-100 text-orange-700',
                                        'cancelado' => 'bg-red-100 text-red-700',
                                    ][$s->estatus] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">
                                    {{ ucfirst(str_replace('_',' ', $s->estatus)) }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.permisos.show', $s) }}" class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-slate-500">No hay solicitudes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-slate-200">{{ $solicitudes->links() }}</div>
    </div>
@endsection
