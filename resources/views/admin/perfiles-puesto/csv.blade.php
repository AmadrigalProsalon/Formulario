@extends('admin.layout')

@section('title', 'Perfiles de puesto CSV')
@section('page_title', 'Perfiles de puesto')
@section('page_description', 'Importa y actualiza perfiles de puesto desde CSV para autollenar requisiciones de personal.')

@section('content')
    @if(session('success'))
        <div class="mb-5 rounded-2xl bg-green-50 border border-green-200 text-green-800 px-5 py-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-2xl bg-red-50 border border-red-200 text-red-800 px-5 py-4">
            <div class="font-semibold mb-2">Revisa lo siguiente:</div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-2">Importar perfiles desde CSV</h2>
            <p class="text-sm text-slate-500 mb-5">
                La primera carga crea los perfiles en la tabla <strong>perfiles_puesto</strong>. Si vuelves a subir el CSV,
                el sistema actualiza los perfiles existentes usando <strong>código</strong> o, si no hay código,
                la combinación de <strong>área + nombre del puesto</strong>.
            </p>

            <form method="POST" action="{{ route('admin.perfiles-puesto.csv.importar') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold mb-1">Archivo CSV</label>
                    <input type="file" name="archivo" accept=".csv,.txt" class="w-full rounded-xl border-slate-300" required>
                    <p class="text-xs text-slate-500 mt-1">
                        Recomendado: usar UTF-8. Columnas mínimas: nombre_puesto y area_departamento.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">
                        Importar / actualizar perfiles
                    </button>

                    <a href="{{ route('admin.perfiles-puesto.csv.plantilla') }}"
                       class="rounded-xl bg-slate-100 text-slate-800 px-5 py-2.5 hover:bg-slate-200">
                        Descargar plantilla CSV
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6 text-blue-900">
            <h3 class="font-bold mb-2">Columnas aceptadas</h3>
            <div class="text-sm space-y-1">
                <div>codigo</div>
                <div>nombre_puesto</div>
                <div>area_departamento</div>
                <div>puesto_reporta</div>
                <div>descripcion_puesto</div>
                <div>objetivo_puesto</div>
                <div>requerimientos_minimos</div>
                <div>habilidades</div>
                <div>responsabilidades</div>
                <div>escolaridad_detectada</div>
                <div>experiencia_detectada</div>
                <div>ingles_detectado</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <form method="GET" action="{{ route('admin.perfiles-puesto.csv') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Buscar</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Puesto, código, área o jefe..." class="w-full rounded-xl border-slate-300">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Departamento</label>
                <select name="departamento" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep }}" @selected($departamento === $dep)>{{ $dep }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">
                    Filtrar
                </button>
                <a href="{{ route('admin.perfiles-puesto.csv') }}" class="rounded-xl bg-slate-100 text-slate-800 px-5 py-2.5 hover:bg-slate-200">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold">Perfiles cargados</h2>
                <p class="text-sm text-slate-500">Estos perfiles alimentan el formulario de Requisición de Personal.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Código</th>
                        <th class="text-left p-4">Puesto</th>
                        <th class="text-left p-4">Departamento</th>
                        <th class="text-left p-4">Reporta a</th>
                        <th class="text-left p-4">Estado</th>
                        <th class="text-left p-4">Última importación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($perfiles as $perfil)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-mono text-xs">{{ $perfil->codigo ?: '-' }}</td>
                            <td class="p-4 font-semibold">{{ $perfil->nombre_puesto }}</td>
                            <td class="p-4">{{ $perfil->area_departamento ?: '-' }}</td>
                            <td class="p-4">{{ $perfil->puesto_reporta ?: '-' }}</td>
                            <td class="p-4">
                                @if($perfil->activo)
                                    <span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs">Activo</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-200 text-slate-700 px-3 py-1 text-xs">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4">{{ $perfil->importado_at ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">Todavía no hay perfiles cargados.</td>
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
