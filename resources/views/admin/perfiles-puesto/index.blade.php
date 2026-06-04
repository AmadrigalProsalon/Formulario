@extends('admin.layout')

@section('title', 'Perfiles de Puesto')
@section('page_title', 'Perfiles de Puesto')
@section('page_description', 'Importa descriptivos de puesto desde Word y úsalos para autollenar requisiciones de personal.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-2">Importar DOCX</h2>
            <p class="text-sm text-slate-500 mb-4">
                Sube un descriptivo de puesto en Word. El sistema extraerá objetivo, requerimientos, habilidades y responsabilidades.
            </p>

            <form method="POST" action="{{ route('admin.perfiles-puesto.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold mb-1">Archivo Word</label>
                    <input type="file" name="archivo" accept=".docx" class="w-full rounded-xl border-slate-300" required>
                    <p class="text-xs text-slate-500 mt-1">Solo archivos .docx</p>
                </div>

                <button type="submit" class="w-full rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">
                    Importar perfil
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-4">Buscar perfiles</h2>

            <form method="GET" action="{{ route('admin.perfiles-puesto.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Buscar</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Puesto, área o reporta a..." class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Área</label>
                    <select name="area" class="w-full rounded-xl border-slate-300">
                        <option value="">Todas</option>
                        @foreach($areas as $a)
                            <option value="{{ $a }}" @selected($area === $a)>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button class="rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">Filtrar</button>
                    <a href="{{ route('admin.perfiles-puesto.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2.5 hover:bg-slate-300">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-lg font-bold">Perfiles importados</h2>
            <p class="text-sm text-slate-500">Estos perfiles se pueden usar para autollenar la Requisición de Personal.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Puesto</th>
                        <th class="text-left p-4">Área</th>
                        <th class="text-left p-4">Reporta a</th>
                        <th class="text-left p-4">Detectado</th>
                        <th class="text-right p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($perfiles as $perfil)
                        <tr class="align-top hover:bg-slate-50">
                            <td class="p-4 font-semibold">
                                {{ $perfil->nombre_puesto }}
                                @if(! $perfil->activo)
                                    <span class="ml-2 rounded-full bg-slate-200 text-slate-700 px-2 py-0.5 text-xs">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4">{{ $perfil->area_departamento ?: 'Sin dato' }}</td>
                            <td class="p-4">{{ $perfil->puesto_reporta ?: 'Sin dato' }}</td>
                            <td class="p-4 text-xs text-slate-600">
                                <div>Objetivo: {{ $perfil->objetivo_puesto ? 'Sí' : 'No' }}</div>
                                <div>Req.: {{ $perfil->requerimientos_minimos ? 'Sí' : 'No' }}</div>
                                <div>Hab.: {{ $perfil->habilidades ? 'Sí' : 'No' }}</div>
                            </td>
                            <td class="p-4 text-right">
                                <details class="text-left inline-block w-full max-w-3xl">
                                    <summary class="cursor-pointer rounded-xl bg-blue-600 text-white px-3 py-2 text-center hover:bg-blue-700">Ver / editar</summary>

                                    <form method="POST" action="{{ route('admin.perfiles-puesto.update', $perfil) }}" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 border border-slate-200 rounded-2xl p-4">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Nombre del puesto</label>
                                            <input name="nombre_puesto" value="{{ $perfil->nombre_puesto }}" class="w-full rounded-xl border-slate-300" required>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Área / Departamento</label>
                                            <input name="area_departamento" value="{{ $perfil->area_departamento }}" class="w-full rounded-xl border-slate-300">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Puesto al que reporta</label>
                                            <input name="puesto_reporta" value="{{ $perfil->puesto_reporta }}" class="w-full rounded-xl border-slate-300">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Nivel de inglés detectado</label>
                                            <input name="ingles_detectado" value="{{ $perfil->ingles_detectado }}" class="w-full rounded-xl border-slate-300">
                                        </div>

                                        @foreach([
                                            'descripcion_puesto' => 'Descripción del puesto',
                                            'objetivo_puesto' => 'Objetivo del puesto',
                                            'requerimientos_minimos' => 'Requerimientos mínimos',
                                            'cualidades' => 'Cualidades',
                                            'habilidades' => 'Habilidades',
                                            'responsabilidades' => 'Responsabilidades',
                                            'escolaridad_detectada' => 'Escolaridad detectada',
                                            'experiencia_detectada' => 'Experiencia detectada',
                                            'software_detectado' => 'Software detectado',
                                        ] as $field => $label)
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-semibold mb-1">{{ $label }}</label>
                                                <textarea name="{{ $field }}" rows="4" class="w-full rounded-xl border-slate-300">{{ $perfil->{$field} }}</textarea>
                                            </div>
                                        @endforeach

                                        <div class="md:col-span-2 flex items-center justify-between">
                                            <label class="inline-flex items-center gap-2">
                                                <input type="checkbox" name="activo" value="1" @checked($perfil->activo) class="rounded">
                                                <span>Activo</span>
                                            </label>

                                            <button class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Guardar cambios</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('admin.perfiles-puesto.destroy', $perfil) }}" onsubmit="return confirm('¿Eliminar este perfil?')" class="mt-3 text-right">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-xl bg-red-600 text-white px-3 py-2 hover:bg-red-700">Eliminar perfil</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">No hay perfiles importados.</td>
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
