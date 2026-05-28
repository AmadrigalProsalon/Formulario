@extends('admin.layout')

@section('title', 'Formularios')
@section('page_title', 'Formularios')
@section('page_description', 'Crea y administra diferentes formularios de RH.')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-bold mb-4">Crear nuevo formulario</h2>

        <form method="POST" action="{{ route('admin.formularios.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-1">Título</label>
                <input type="text" name="titulo" class="w-full rounded-xl border-slate-300" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Slug URL</label>
                <input type="text" name="slug" placeholder="ej. encuesta-clima-laboral" class="w-full rounded-xl border-slate-300">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Correo destino</label>
                <input type="email" name="mail_to" placeholder="rh@empresa.com" class="w-full rounded-xl border-slate-300">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Descripción</label>
                <textarea name="descripcion" rows="2" class="w-full rounded-xl border-slate-300"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Ruta plantilla Word opcional</label>
                <input type="text" name="template_path" placeholder="storage/app/templates/archivo.docx" class="w-full rounded-xl border-slate-300">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="activo" value="1" class="rounded" checked>
                <span>Activo</span>
            </div>

            <div class="md:col-span-3 flex justify-end">
                <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">
                    Crear formulario
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-lg font-bold">Formularios registrados</h2>
            <p class="text-sm text-slate-500">Cada formulario tiene sus propios campos y respuestas.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Formulario</th>
                        <th class="text-left p-4">URL pública</th>
                        <th class="text-left p-4">Campos</th>
                        <th class="text-left p-4">Respuestas</th>
                        <th class="text-left p-4">Estado</th>
                        <th class="text-right p-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($formularios as $formulario)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <form id="formulario-{{ $formulario->id }}" method="POST" action="{{ route('admin.formularios.update', $formulario) }}" class="space-y-2">
                                    @csrf
                                    @method('PUT')

                                    <input type="text" name="titulo" value="{{ $formulario->titulo }}" class="w-full rounded-xl border-slate-300 font-semibold">

                                    <input type="text" name="slug" value="{{ $formulario->slug }}" class="w-full rounded-xl border-slate-300 text-xs font-mono">

                                    <textarea name="descripcion" rows="2" class="w-full rounded-xl border-slate-300 text-xs">{{ $formulario->descripcion }}</textarea>

                                    <input type="email" name="mail_to" value="{{ $formulario->mail_to }}" placeholder="Correo destino" class="w-full rounded-xl border-slate-300 text-xs">

                                    <input type="text" name="template_path" value="{{ $formulario->template_path }}" placeholder="Ruta plantilla Word" class="w-full rounded-xl border-slate-300 text-xs">

                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="activo" value="1" class="rounded" @checked($formulario->activo)>
                                        <span>Activo</span>
                                    </label>
                                </form>
                            </td>

                            <td class="p-4">
                                <a href="{{ route('form.show', $formulario) }}" target="_blank" class="text-blue-600 underline">
                                    {{ route('form.show', $formulario) }}
                                </a>

                                @if($formulario->es_default)
                                    <div class="mt-2 inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs">
                                        Predeterminado
                                    </div>
                                @endif
                            </td>

                            <td class="p-4">{{ $formulario->fields_count }}</td>
                            <td class="p-4">{{ $formulario->respuestas_count }}</td>

                            <td class="p-4">
                                @if($formulario->activo)
                                    <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs">Activo</span>
                                @else
                                    <span class="rounded-full bg-slate-200 text-slate-700 px-3 py-1 text-xs">Inactivo</span>
                                @endif
                            </td>

                            <td class="p-4 text-right">
                                <div class="flex flex-col gap-2 items-end">
                                    <button form="formulario-{{ $formulario->id }}" class="rounded-xl bg-slate-950 text-white px-3 py-2 hover:bg-slate-800">
                                        Guardar
                                    </button>

                                    <a href="{{ route('admin.fields.index', ['formulario_id' => $formulario->id]) }}" class="rounded-xl bg-blue-600 text-white px-3 py-2 hover:bg-blue-700">
                                        Campos
                                    </a>

                                    <a href="{{ route('admin.respuestas.index', ['formulario_id' => $formulario->id]) }}" class="rounded-xl bg-green-600 text-white px-3 py-2 hover:bg-green-700">
                                        Respuestas
                                    </a>

                                    @if(! $formulario->es_default)
                                        <form method="POST" action="{{ route('admin.formularios.default', $formulario) }}">
                                            @csrf
                                            <button class="rounded-xl bg-yellow-500 text-white px-3 py-2 hover:bg-yellow-600">
                                                Hacer default
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.formularios.toggle', $formulario) }}">
                                        @csrf
                                        <button class="rounded-xl bg-slate-500 text-white px-3 py-2 hover:bg-slate-600">
                                            {{ $formulario->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    @if(! $formulario->es_default)
                                        <form method="POST" action="{{ route('admin.formularios.destroy', $formulario) }}" onsubmit="return confirm('¿Eliminar este formulario?')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="rounded-xl bg-red-600 text-white px-3 py-2 hover:bg-red-700">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                No hay formularios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-200">
            {{ $formularios->links() }}
        </div>
    </div>
@endsection
