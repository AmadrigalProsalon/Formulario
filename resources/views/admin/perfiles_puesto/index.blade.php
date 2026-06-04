@extends('admin.layout')

@section('title', 'Perfiles de puesto')
@section('page_title', 'Perfiles de puesto')
@section('page_description', 'Importa descriptivos de puesto desde Word para autollenar requisiciones de personal.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-2">Importar perfil desde Word</h2>
            <p class="text-sm text-slate-500 mb-4">
                Sube un archivo .docx de descriptivo de puesto. El sistema intentará extraer área, puesto al que reporta, descripción, objetivo, requerimientos y responsabilidades.
            </p>

            <form method="POST" action="{{ route('admin.perfiles-puesto.importar') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold mb-1">Archivo DOCX</label>
                    <input type="file" name="archivo" accept=".docx" class="w-full rounded-xl border border-slate-300 p-2" required>
                </div>

                <button type="submit" class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">
                    Importar perfil
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-4">Buscar perfiles</h2>

            <form method="GET" action="{{ route('admin.perfiles-puesto.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Buscar</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Puesto, área o reporta a..." class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Área</label>
                    <select name="area" class="w-full rounded-xl border-slate-300">
                        <option value="">Todas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}" @selected(request('area') === $area)>{{ $area }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">Filtrar</button>
                    <a href="{{ route('admin.perfiles-puesto.index') }}" class="rounded-xl bg-slate-200 px-4 py-2.5 hover:bg-slate-300">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-lg font-bold">Perfiles importados</h2>
            <p class="text-sm text-slate-500">Estos perfiles se usan para autollenar el formulario de Requisición de Personal.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Puesto</th>
                        <th class="text-left p-4">Área</th>
                        <th class="text-left p-4">Reporta a</th>
                        <th class="text-left p-4">Estado</th>
                        <th class="text-right p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($perfiles as $perfil)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <div class="font-semibold">{{ $perfil->nombre_puesto }}</div>
                                <div class="text-xs text-slate-500">{{ $perfil->responsabilidades_count }} responsabilidades detectadas</div>
                            </td>
                            <td class="p-4">{{ $perfil->area_departamento ?: 'Sin área' }}</td>
                            <td class="p-4">{{ $perfil->puesto_reporta ?: 'Sin dato' }}</td>
                            <td class="p-4">
                                @if($perfil->activo)
                                    <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs">Activo</span>
                                @else
                                    <span class="rounded-full bg-slate-200 text-slate-700 px-3 py-1 text-xs">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.perfiles-puesto.show', $perfil) }}" class="inline-flex rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">
                                    Revisar / editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">No hay perfiles importados todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-200">
            {{ $perfiles->links() }}
        </div>
    </div>
@endsection
