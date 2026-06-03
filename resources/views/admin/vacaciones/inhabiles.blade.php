@extends('admin.layout')

@section('title', 'Días inhábiles')
@section('page_title', 'Días inhábiles de vacaciones')
@section('page_description', 'Registra fechas que no deben descontarse al calcular vacaciones.')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-bold mb-4">Agregar día inhábil</h2>
        <form method="POST" action="{{ route('admin.vacaciones.inhabiles.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div><label class="block text-sm font-semibold mb-1">Fecha</label><input type="date" name="fecha" class="w-full rounded-xl border-slate-300" required></div>
            <div><label class="block text-sm font-semibold mb-1">Nombre</label><input name="nombre" placeholder="Ej. Día del Trabajo" class="w-full rounded-xl border-slate-300" required></div>
            <div><label class="block text-sm font-semibold mb-1">Tipo</label><input name="tipo" value="oficial" class="w-full rounded-xl border-slate-300"></div>
            <div class="flex items-end gap-4"><label class="inline-flex items-center gap-2"><input type="checkbox" name="activo" value="1" checked><span>Activo</span></label><button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Agregar</button></div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200"><h2 class="text-lg font-bold">Fechas registradas</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left p-4">Fecha</th><th class="text-left p-4">Nombre</th><th class="text-left p-4">Tipo</th><th class="text-left p-4">Estado</th><th class="text-right p-4">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dias as $dia)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">{{ $dia->fecha?->format('d/m/Y') }}</td>
                            <td class="p-4 font-semibold">{{ $dia->nombre }}</td>
                            <td class="p-4">{{ $dia->tipo }}</td>
                            <td class="p-4">{{ $dia->activo ? 'Activo' : 'Inactivo' }}</td>
                            <td class="p-4 text-right">
                                <form method="POST" action="{{ route('admin.vacaciones.inhabiles.destroy', $dia) }}" onsubmit="return confirm('¿Eliminar esta fecha?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-xl bg-red-600 text-white px-3 py-2 hover:bg-red-700">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">No hay días inhábiles registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-slate-200">{{ $dias->links() }}</div>
    </div>
@endsection
