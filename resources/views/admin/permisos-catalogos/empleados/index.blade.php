@extends('admin.layout')

@section('title', 'Empleados')
@section('page_title', 'Empleados')
@section('page_description', 'Filtra por departamento para editar empleados sin cargar todos de golpe.')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.permisos.empleados.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Buscar</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-xl border-slate-300" placeholder="Nombre, correo, número">
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
                <label class="block text-sm font-semibold mb-1">Estado</label>
                <select name="activo" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    <option value="1" @selected(($filters['activo'] ?? '') === '1')>Activos</option>
                    <option value="0" @selected(($filters['activo'] ?? '') === '0')>Inactivos</option>
                </select>
            </div>

            <div class="md:col-span-2 flex items-end gap-2">
                <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Filtrar</button>
                <a href="{{ route('admin.permisos.empleados.index') }}" class="rounded-xl bg-slate-200 px-5 py-2.5 hover:bg-slate-300">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-lg font-bold">Listado de empleados</h2>
            <p class="text-sm text-slate-500">Mostrando {{ $empleados->count() }} de {{ $empleados->total() }} registros.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Empleado</th>
                        <th class="text-left p-4">Departamento</th>
                        <th class="text-left p-4">Líder</th>
                        <th class="text-left p-4">Puesto / ingreso</th>
                        <th class="text-right p-4">Guardar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($empleados as $empleado)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <form id="empleado-{{ $empleado->id }}" method="POST" action="{{ route('admin.permisos.empleados.update', $empleado) }}" class="space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="numero_empleado" value="{{ $empleado->numero_empleado }}" placeholder="Número" class="w-full rounded-xl border-slate-300 text-xs">
                                    <input type="text" name="nombre" value="{{ $empleado->nombre }}" placeholder="Nombre" class="w-full rounded-xl border-slate-300 font-semibold" required>
                                    <input type="email" name="correo" value="{{ $empleado->correo }}" placeholder="Correo" class="w-full rounded-xl border-slate-300 text-xs">
                            </td>
                            <td class="p-4">
                                    <select name="area_id" class="w-full rounded-xl border-slate-300">
                                        <option value="">Sin departamento</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" @selected($empleado->area_id == $area->id)>{{ $area->nombre }}</option>
                                        @endforeach
                                    </select>
                            </td>
                            <td class="p-4">
                                    <select name="lider_id" class="w-full rounded-xl border-slate-300">
                                        <option value="">Sin líder</option>
                                        @foreach($lideres as $lider)
                                            <option value="{{ $lider->id }}" @selected($empleado->lider_id == $lider->id)>{{ $lider->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <label class="inline-flex items-center gap-2 mt-2">
                                        <input type="checkbox" name="es_lider" value="1" class="rounded" @checked($empleado->es_lider)>
                                        <span>Es líder</span>
                                    </label>
                            </td>
                            <td class="p-4">
                                    <input type="text" name="puesto" value="{{ $empleado->puesto }}" placeholder="Puesto" class="w-full rounded-xl border-slate-300 mb-2">
                                    <input type="date" name="fecha_ingreso" value="{{ $empleado->fecha_ingreso?->format('Y-m-d') }}" class="w-full rounded-xl border-slate-300 mb-2">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="activo" value="1" class="rounded" @checked($empleado->activo)>
                                        <span>Activo</span>
                                    </label>
                                </form>
                            </td>
                            <td class="p-4 text-right">
                                <button form="empleado-{{ $empleado->id }}" class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Guardar</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">No hay empleados con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-slate-200">{{ $empleados->links() }}</div>
    </div>
@endsection
