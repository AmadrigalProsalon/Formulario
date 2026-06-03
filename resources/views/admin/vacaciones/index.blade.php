@extends('admin.layout')

@section('title', 'Vacaciones')
@section('page_title', 'Solicitudes de vacaciones')
@section('page_description', 'Consulta, aprueba o rechaza solicitudes de vacaciones.')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('admin.vacaciones.empleados.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:bg-slate-50">
            <div class="text-lg font-bold">Empleados</div>
            <div class="text-sm text-slate-500">Alta y edición de colaboradores</div>
        </a>
        <a href="{{ route('admin.vacaciones.inhabiles.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:bg-slate-50">
            <div class="text-lg font-bold">Días inhábiles</div>
            <div class="text-sm text-slate-500">Fechas que no descuentan vacaciones</div>
        </a>
        <a href="{{ route('vacaciones.create') }}" target="_blank" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:bg-slate-50">
            <div class="text-lg font-bold">Formulario público</div>
            <div class="text-sm text-slate-500">Abrir solicitud de vacaciones</div>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.vacaciones.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Buscar</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre, correo o número de empleado" class="w-full rounded-xl border-slate-300">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Estatus</label>
                <select name="estatus" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    @foreach(['pendiente', 'aprobada', 'rechazada', 'cancelada'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filters['estatus'] ?? '') === $estatus)>{{ ucfirst($estatus) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">Filtrar</button>
                <a href="{{ route('admin.vacaciones.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2.5 hover:bg-slate-300">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-lg font-bold">Solicitudes</h2>
            <p class="text-sm text-slate-500">Mostrando {{ $solicitudes->count() }} de {{ $solicitudes->total() }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Folio</th>
                        <th class="text-left p-4">Empleado</th>
                        <th class="text-left p-4">Fechas</th>
                        <th class="text-left p-4">Días</th>
                        <th class="text-left p-4">Estatus</th>
                        <th class="text-left p-4">Comentarios</th>
                        <th class="text-right p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($solicitudes as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-semibold">#{{ $s->id }}</td>
                            <td class="p-4">
                                <div class="font-semibold">{{ $s->empleado?->nombre }}</div>
                                <div class="text-xs text-slate-500">{{ $s->empleado?->numero_empleado }} · {{ $s->empleado?->correo }}</div>
                            </td>
                            <td class="p-4">
                                {{ $s->fecha_inicio?->format('d/m/Y') }} al {{ $s->fecha_fin?->format('d/m/Y') }}
                            </td>
                            <td class="p-4">{{ $s->dias_solicitados }}</td>
                            <td class="p-4">
                                @php
                                    $badge = [
                                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                                        'aprobada' => 'bg-green-100 text-green-800',
                                        'rechazada' => 'bg-red-100 text-red-800',
                                        'cancelada' => 'bg-slate-200 text-slate-800',
                                    ][$s->estatus] ?? 'bg-slate-200 text-slate-800';
                                @endphp
                                <span class="rounded-full px-3 py-1 text-xs {{ $badge }}">{{ ucfirst($s->estatus) }}</span>
                            </td>
                            <td class="p-4 max-w-xs">
                                <div class="text-slate-700">{{ $s->comentarios_empleado }}</div>
                                @if($s->comentarios_admin)
                                    <div class="text-xs text-slate-500 mt-2"><strong>Admin:</strong> {{ $s->comentarios_admin }}</div>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                @if($s->estatus === 'pendiente')
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.vacaciones.solicitudes.aprobar', $s) }}">
                                            @csrf
                                            <input type="hidden" name="comentarios_admin" value="Aprobado">
                                            <button class="rounded-xl bg-green-600 text-white px-3 py-2 hover:bg-green-700">Aprobar</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.vacaciones.solicitudes.rechazar', $s) }}" onsubmit="return confirm('¿Rechazar esta solicitud?')">
                                            @csrf
                                            <input type="hidden" name="comentarios_admin" value="Rechazado">
                                            <button class="rounded-xl bg-red-600 text-white px-3 py-2 hover:bg-red-700">Rechazar</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">No hay solicitudes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-200">
            {{ $solicitudes->links() }}
        </div>
    </div>
@endsection
