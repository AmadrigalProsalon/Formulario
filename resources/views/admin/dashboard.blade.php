@extends('admin.layout')

@section('title', 'Respuestas RH')
@section('page_title', 'Respuestas del formulario')
@section('page_description', 'Consulta, filtra, revisa y exporta las respuestas recibidas.')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5"><div class="text-sm text-slate-500">Total respuestas</div><div class="text-3xl font-bold mt-2">{{ $stats['total'] ?? 0 }}</div></div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5"><div class="text-sm text-slate-500">Hoy</div><div class="text-3xl font-bold mt-2">{{ $stats['hoy'] ?? 0 }}</div></div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5"><div class="text-sm text-slate-500">Últimos 7 días</div><div class="text-3xl font-bold mt-2">{{ $stats['semana'] ?? 0 }}</div></div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5"><div class="text-sm text-slate-500">Formularios</div><div class="text-3xl font-bold mt-2">{{ $stats['formularios'] ?? 0 }}</div></div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.respuestas.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div><label class="block text-sm font-semibold mb-1">Buscar</label><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="ID, puesto, nombre..." class="w-full rounded-xl border-slate-300"></div>
            <div><label class="block text-sm font-semibold mb-1">Formulario</label><select name="formulario_id" class="w-full rounded-xl border-slate-300"><option value="">Todos</option>@foreach($formularios as $formulario)<option value="{{ $formulario->id }}" @selected(($filters['formulario_id'] ?? '') == $formulario->id)>{{ $formulario->titulo }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-semibold mb-1">Desde</label><input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}" class="w-full rounded-xl border-slate-300"></div>
            <div><label class="block text-sm font-semibold mb-1">Hasta</label><input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}" class="w-full rounded-xl border-slate-300"></div>
            <div><label class="block text-sm font-semibold mb-1">Departamento</label><select name="departamento" class="w-full rounded-xl border-slate-300"><option value="">Todos</option>@foreach($departamentos as $departamento)<option value="{{ $departamento }}" @selected(($filters['departamento'] ?? '') === $departamento)>{{ $departamento }}</option>@endforeach</select></div>
            <div class="flex items-end gap-2"><button type="submit" class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">Filtrar</button><a href="{{ route('admin.respuestas.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2.5 hover:bg-slate-300">Limpiar</a></div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <div><h2 class="text-lg font-bold">Listado de respuestas</h2><p class="text-sm text-slate-500">Mostrando {{ $respuestas->count() }} de {{ $respuestas->total() }}</p></div>
            <a href="{{ route('admin.respuestas.export', request()->query()) }}" class="rounded-xl bg-green-600 text-white px-4 py-2.5 hover:bg-green-700">Exportar CSV</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left p-4">ID</th><th class="text-left p-4">Formulario</th><th class="text-left p-4">Fecha</th><th class="text-left p-4">Departamento</th><th class="text-left p-4">Puesto</th><th class="text-left p-4">Horario</th><th class="text-right p-4">Acción</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($respuestas as $r)
                        @php $data = is_array($r->data) ? $r->data : json_decode($r->data ?? '[]', true); @endphp
                        <tr class="hover:bg-slate-50"><td class="p-4 font-semibold">#{{ $r->id }}</td><td class="p-4">{{ $r->formulario?->titulo ?? 'Sin formulario' }}</td><td class="p-4">{{ $r->created_at?->format('d/m/Y H:i') }}</td><td class="p-4">{{ $r->departamento ?: ($data['departamento'] ?? 'Sin dato') }}</td><td class="p-4">{{ $r->puesto ?: ($data['puesto'] ?? 'Sin dato') }}</td><td class="p-4">{{ $r->horario ?: ($data['horario'] ?? 'Sin dato') }}</td><td class="p-4 text-right"><a href="{{ route('admin.respuesta.view', $r->id) }}" class="inline-flex rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Ver detalle</a></td></tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-slate-500">No hay respuestas con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-200">{{ $respuestas->links() }}</div>
    </div>
@endsection
