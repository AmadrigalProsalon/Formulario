@extends('admin.layout')

@section('title', 'Revisar perfil')
@section('page_title', 'Revisar perfil de puesto')
@section('page_description', 'Valida y corrige la información extraída del documento Word.')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.perfiles-puesto.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-300">Volver</a>

        @if($perfil->archivo_original_path)
            <a href="{{ route('admin.perfiles-puesto.descargar-original', $perfil) }}" class="rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
                Descargar Word original
            </a>
        @endif

        @if($perfil->activo)
            <form method="POST" action="{{ route('admin.perfiles-puesto.desactivar', $perfil) }}">
                @csrf
                <button class="rounded-xl bg-yellow-500 text-white px-4 py-2 hover:bg-yellow-600">Desactivar</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.perfiles-puesto.activar', $perfil) }}">
                @csrf
                <button class="rounded-xl bg-green-600 text-white px-4 py-2 hover:bg-green-700">Activar</button>
            </form>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.perfiles-puesto.update', $perfil) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-4">Datos generales</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre del puesto</label>
                    <input type="text" name="nombre_puesto" value="{{ old('nombre_puesto', $perfil->nombre_puesto) }}" class="w-full rounded-xl border-slate-300" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Área / departamento</label>
                    <input type="text" name="area_departamento" value="{{ old('area_departamento', $perfil->area_departamento) }}" class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Puesto al que reporta</label>
                    <input type="text" name="puesto_reporta" value="{{ old('puesto_reporta', $perfil->puesto_reporta) }}" class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Organización</label>
                    <input type="text" name="organizacion" value="{{ old('organizacion', $perfil->organizacion) }}" class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Código</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $perfil->codigo) }}" class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Fecha elaboración</label>
                    <input type="text" name="fecha_elaboracion" value="{{ old('fecha_elaboracion', $perfil->fecha_elaboracion) }}" class="w-full rounded-xl border-slate-300">
                </div>

                <div class="flex items-center gap-2 md:col-span-3">
                    <input type="checkbox" name="activo" value="1" class="rounded" @checked(old('activo', $perfil->activo))>
                    <span>Perfil activo para usar en requisiciones</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-4">Contenido del perfil</h2>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Descripción del puesto</label>
                    <textarea name="descripcion_puesto" rows="4" class="w-full rounded-xl border-slate-300">{{ old('descripcion_puesto', $perfil->descripcion_puesto) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Objetivo del puesto</label>
                    <textarea name="objetivo_puesto" rows="4" class="w-full rounded-xl border-slate-300">{{ old('objetivo_puesto', $perfil->objetivo_puesto) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Requerimientos mínimos</label>
                    <textarea name="requerimientos_minimos" rows="5" class="w-full rounded-xl border-slate-300">{{ old('requerimientos_minimos', $perfil->requerimientos_minimos) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Cualidades</label>
                        <textarea name="cualidades" rows="6" class="w-full rounded-xl border-slate-300">{{ old('cualidades', $perfil->cualidades) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Habilidades</label>
                        <textarea name="habilidades" rows="6" class="w-full rounded-xl border-slate-300">{{ old('habilidades', $perfil->habilidades) }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Responsabilidades y actividades</label>
                    <textarea name="responsabilidades_text" rows="8" class="w-full rounded-xl border-slate-300">{{ old('responsabilidades_text', $perfil->responsabilidades_text) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button class="rounded-xl bg-slate-950 text-white px-6 py-3 hover:bg-slate-800">Guardar perfil</button>
        </div>
    </form>
@endsection
