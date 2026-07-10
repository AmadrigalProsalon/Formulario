@extends('admin.layout')

@section('title', 'Calendario laboral')
@section('page_title', 'Calendario laboral')
@section('page_description', 'Configura días de trabajo por área y registra días festivos o inhábiles.')

@section('content')
    @if(session('success'))
        <div class="mb-5 rounded-2xl bg-emerald-100 border border-emerald-300 text-emerald-800 px-5 py-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-2xl bg-red-100 border border-red-300 text-red-800 px-5 py-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-lg font-bold mb-1">Horarios por área</h2>
            <p class="text-sm text-slate-500 mb-5">El horario individual del empleado tiene prioridad sobre el horario del área.</p>

            <div class="space-y-4">
                @foreach($areas as $area)
                    <form method="POST" action="{{ route('admin.permisos.calendario-laboral.areas.update', $area) }}" class="rounded-2xl border border-slate-200 p-4">
                        @csrf
                        @method('PUT')
                        @php($diasArea = $area->dias_laborales ?? [1,2,3,4,5])

                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $area->nombre }}</div>
                                <div class="flex flex-wrap gap-3 mt-2 text-sm">
                                    @foreach($nombresDias as $numero => $nombre)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="checkbox" name="dias_laborales[]" value="{{ $numero }}" class="rounded" @checked(in_array($numero, $diasArea, true))>
                                            <span>{{ $nombre }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Guardar</button>
                        </div>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h2 class="text-lg font-bold mb-1">Agregar día inhábil / festivo</h2>
                <p class="text-sm text-slate-500 mb-4">Estos días serán bloqueados para solicitudes de vacaciones.</p>

                <form method="POST" action="{{ route('admin.permisos.calendario-laboral.inhabiles.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold mb-1">Fecha</label>
                        <input type="date" name="fecha" class="w-full rounded-xl border-slate-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nombre</label>
                        <input type="text" name="nombre" class="w-full rounded-xl border-slate-300" placeholder="Ej. Navidad" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tipo</label>
                        <input type="text" name="tipo" class="w-full rounded-xl border-slate-300" value="oficial">
                    </div>
                    <div class="md:col-span-3 text-right">
                        <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Guardar día inhábil</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-200">
                    <h2 class="text-lg font-bold">Días inhábiles registrados</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left p-3">Fecha</th>
                                <th class="text-left p-3">Nombre</th>
                                <th class="text-left p-3">Tipo</th>
                                <th class="text-right p-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($diasInhabiles as $dia)
                                <tr>
                                    <td class="p-3">{{ $dia->fecha?->format('d/m/Y') }}</td>
                                    <td class="p-3 font-semibold">{{ $dia->nombre }}</td>
                                    <td class="p-3">{{ $dia->tipo }}</td>
                                    <td class="p-3 text-right">
                                        <form method="POST" action="{{ route('admin.permisos.calendario-laboral.inhabiles.destroy', $dia) }}" onsubmit="return confirm('¿Eliminar este día inhábil?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-rose-600 hover:text-rose-800 font-semibold">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-6 text-center text-slate-500">No hay días inhábiles registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-200">{{ $diasInhabiles->links() }}</div>
            </div>
        </div>
    </div>
@endsection
