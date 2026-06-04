@extends('admin.layout')

@section('title', 'Permisos y ausencias')
@section('page_title', 'Permisos y ausencias')
@section('page_description', 'Control de vacaciones, permisos con goce, sin goce y formatos pendientes de RH.')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5"><div class="text-sm text-slate-500">Total</div><div class="text-3xl font-bold">{{ $stats['total'] }}</div></div>
        <div class="bg-yellow-50 rounded-2xl border border-yellow-200 p-5"><div class="text-sm text-yellow-700">Pendientes</div><div class="text-3xl font-bold text-yellow-900">{{ $stats['pendientes'] }}</div></div>
        <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-5"><div class="text-sm text-emerald-700">Firmados</div><div class="text-3xl font-bold text-emerald-900">{{ $stats['firmados'] }}</div></div>
        <div class="bg-blue-50 rounded-2xl border border-blue-200 p-5"><div class="text-sm text-blue-700">Recibidos RH</div><div class="text-3xl font-bold text-blue-900">{{ $stats['recibidos'] }}</div></div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar empleado..." class="rounded-xl border-slate-300">
            <select name="tipo_permiso_id" class="rounded-xl border-slate-300"><option value="">Todos los tipos</option>@foreach($tipos as $tipo)<option value="{{ $tipo->id }}" @selected(request('tipo_permiso_id') == $tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select>
            <select name="estatus" class="rounded-xl border-slate-300"><option value="">Todos los estatus</option>@foreach(['pendiente_firma_colaborador','pendiente_firma_lider','firmado_completo','formato_recibido','formato_pendiente','con_observaciones','cancelado'] as $estatus)<option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ ucfirst(str_replace('_',' ', $estatus)) }}</option>@endforeach</select>
            <button class="rounded-xl bg-slate-950 text-white px-4 py-2">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex justify-between items-center"><div><h2 class="text-lg font-bold">Solicitudes</h2><p class="text-sm text-slate-500">{{ $solicitudes->total() }} registros</p></div><a href="{{ route('permisos.solicitud') }}" target="_blank" class="rounded-xl bg-blue-600 text-white px-4 py-2">Nueva solicitud</a></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left p-4">ID</th><th class="text-left p-4">Empleado</th><th class="text-left p-4">Tipo</th><th class="text-left p-4">Fechas</th><th class="text-left p-4">Firmas</th><th class="text-left p-4">Estatus</th><th class="text-right p-4">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($solicitudes as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-bold">#{{ $s->id }}</td>
                            <td class="p-4"><div class="font-semibold">{{ $s->empleado?->nombre }}</div><div class="text-xs text-slate-500">{{ $s->empleado?->area?->nombre }} · Líder: {{ $s->lider?->nombre ?? 'Sin líder' }}</div></td>
                            <td class="p-4">{{ $s->tipoPermiso?->nombre }}</td>
                            <td class="p-4">{{ $s->fecha_inicio?->format('d/m/Y') }} - {{ $s->fecha_fin?->format('d/m/Y') }}<div class="text-xs text-slate-500">{{ $s->dias_solicitados }} días</div></td>
                            <td class="p-4">{{ $s->firmas->where('estatus','firmado')->count() }} / {{ $s->firmas->count() }}</td>
                            <td class="p-4"><span class="inline-flex border rounded-full px-3 py-1 text-xs {{ $s->badge_class }}">{{ $s->estatus_label }}</span></td>
                            <td class="p-4 text-right"><a href="{{ route('admin.permisos.show', $s) }}" class="rounded-xl bg-slate-950 text-white px-4 py-2">Ver</a></td>
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
