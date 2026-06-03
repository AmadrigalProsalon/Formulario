@extends('admin.layout')

@section('title', 'Empleados vacaciones')
@section('page_title', 'Empleados y saldos')
@section('page_description', 'Administra colaboradores y revisa sus días disponibles.')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-bold mb-4">Agregar empleado</h2>
        <form method="POST" action="{{ route('admin.vacaciones.empleados.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div><label class="block text-sm font-semibold mb-1">Número</label><input name="numero_empleado" class="w-full rounded-xl border-slate-300" required></div>
            <div><label class="block text-sm font-semibold mb-1">Nombre</label><input name="nombre" class="w-full rounded-xl border-slate-300" required></div>
            <div><label class="block text-sm font-semibold mb-1">Correo</label><input type="email" name="correo" class="w-full rounded-xl border-slate-300"></div>
            <div><label class="block text-sm font-semibold mb-1">Fecha ingreso</label><input type="date" name="fecha_ingreso" class="w-full rounded-xl border-slate-300"></div>
            <div><label class="block text-sm font-semibold mb-1">Departamento</label><input name="departamento" class="w-full rounded-xl border-slate-300"></div>
            <div><label class="block text-sm font-semibold mb-1">Puesto</label><input name="puesto" class="w-full rounded-xl border-slate-300"></div>
            <div class="flex items-center gap-2 pt-6"><input type="checkbox" name="activo" value="1" checked><span>Activo</span></div>
            <div class="flex items-end"><button class="w-full rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Crear empleado</button></div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.vacaciones.empleados.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-3"><label class="block text-sm font-semibold mb-1">Buscar</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-xl border-slate-300" placeholder="Nombre, número, correo o departamento"></div>
            <div class="flex items-end gap-2"><button class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">Buscar</button><a href="{{ route('admin.vacaciones.empleados.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2.5 hover:bg-slate-300">Limpiar</a></div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200"><h2 class="text-lg font-bold">Empleados</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left p-4">Datos</th><th class="text-left p-4">Saldo</th><th class="text-left p-4">Ajuste</th><th class="text-right p-4">Guardar</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($empleados as $empleado)
                        @php($saldo = $resumenes[$empleado->id])
                        <tr class="hover:bg-slate-50 align-top">
                            <td class="p-4 min-w-[420px]">
                                <form id="empleado-{{ $empleado->id }}" method="POST" action="{{ route('admin.vacaciones.empleados.update', $empleado) }}" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @csrf @method('PUT')
                                    <input name="numero_empleado" value="{{ $empleado->numero_empleado }}" class="rounded-xl border-slate-300" required>
                                    <input name="nombre" value="{{ $empleado->nombre }}" class="rounded-xl border-slate-300" required>
                                    <input type="email" name="correo" value="{{ $empleado->correo }}" class="rounded-xl border-slate-300">
                                    <input type="date" name="fecha_ingreso" value="{{ optional($empleado->fecha_ingreso)->format('Y-m-d') }}" class="rounded-xl border-slate-300">
                                    <input name="departamento" value="{{ $empleado->departamento }}" class="rounded-xl border-slate-300">
                                    <input name="puesto" value="{{ $empleado->puesto }}" class="rounded-xl border-slate-300">
                                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="activo" value="1" @checked($empleado->activo)> <span>Activo</span></label>
                                </form>
                            </td>
                            <td class="p-4 min-w-[220px]">
                                <div>Totales: <strong>{{ $saldo['dias_totales'] }}</strong></div>
                                <div>Usados: <strong>{{ $saldo['dias_usados'] }}</strong></div>
                                <div>Pendientes: <strong>{{ $saldo['dias_pendientes'] }}</strong></div>
                                <div>Disponibles: <strong class="text-green-700 text-lg">{{ $saldo['dias_disponibles'] }}</strong></div>
                                <div class="text-xs text-slate-500">Ajustes: {{ $saldo['dias_ajuste'] }}</div>
                            </td>
                            <td class="p-4 min-w-[260px]">
                                <form method="POST" action="{{ route('admin.vacaciones.empleados.ajustes.store', $empleado) }}" class="space-y-2">
                                    @csrf
                                    <input type="number" name="anio" value="{{ now()->year }}" class="w-full rounded-xl border-slate-300" required>
                                    <input type="number" step="0.5" name="dias" placeholder="Días (+ o -)" class="w-full rounded-xl border-slate-300" required>
                                    <input name="comentario" placeholder="Motivo" class="w-full rounded-xl border-slate-300">
                                    <button class="w-full rounded-xl bg-blue-600 text-white px-3 py-2 hover:bg-blue-700">Agregar ajuste</button>
                                </form>
                            </td>
                            <td class="p-4 text-right"><button form="empleado-{{ $empleado->id }}" class="rounded-xl bg-slate-950 text-white px-3 py-2 hover:bg-slate-800">Guardar</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-slate-200">{{ $empleados->links() }}</div>
    </div>
@endsection
