@extends('admin.layout')

@section('title', 'Editar campo')
@section('page_title', 'Editar campo')
@section('page_description', 'Modifica la configuración de un campo.')

@section('content')
    <div class="mb-5"><a href="{{ route('admin.fields.index', ['formulario_id' => $field->formulario_id]) }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-300">Volver</a></div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <form method="POST" action="{{ route('admin.fields.update', $field->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div><label class="block text-sm font-semibold mb-1">Formulario</label><select name="formulario_id" class="w-full rounded-xl border-slate-300" required>@foreach($formularios as $formulario)<option value="{{ $formulario->id }}" @selected(old('formulario_id', $field->formulario_id) == $formulario->id)>{{ $formulario->titulo }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-semibold mb-1">Nombre interno</label><input type="text" name="name" value="{{ old('name', $field->name) }}" class="w-full rounded-xl border-slate-300" required></div>
            <div><label class="block text-sm font-semibold mb-1">Etiqueta</label><input type="text" name="label" value="{{ old('label', $field->label) }}" class="w-full rounded-xl border-slate-300" required></div>
            <div><label class="block text-sm font-semibold mb-1">Sección</label><input type="text" name="section" value="{{ old('section', $field->section) }}" class="w-full rounded-xl border-slate-300" required></div>
            <div><label class="block text-sm font-semibold mb-1">Tipo</label><select name="type" class="w-full rounded-xl border-slate-300" required>@foreach(['text', 'textarea', 'select', 'radio', 'checkbox', 'email', 'number', 'date', 'tel'] as $type)<option value="{{ $type }}" @selected(old('type', $field->type) === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-semibold mb-1">Data source</label><input type="text" name="data_source" value="{{ old('data_source', $field->data_source) }}" placeholder="db, catalogos o valores separados por coma" class="w-full rounded-xl border-slate-300"><p class="text-xs text-slate-500 mt-1">Para traer opciones desde la tabla catalogos, usa <strong>db</strong> o <strong>catalogos</strong>.</p></div>
            <div><label class="block text-sm font-semibold mb-1">Data table / tipo</label><input type="text" name="data_table" value="{{ old('data_table', $field->data_table) }}" placeholder="ej. departamento" class="w-full rounded-xl border-slate-300"><p class="text-xs text-slate-500 mt-1">Debe coincidir con la columna <strong>tipo</strong> de la tabla catalogos.</p></div>
            <div class="flex items-center gap-4"><label class="inline-flex items-center gap-2"><input type="checkbox" name="required" value="1" class="rounded" @checked(old('required', $field->required))><span>Requerido</span></label><label class="inline-flex items-center gap-2"><input type="checkbox" name="visible" value="1" class="rounded" @checked(old('visible', $field->visible))><span>Visible</span></label></div>
            <div class="md:col-span-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 p-4 text-sm"><div class="font-semibold mb-1">Para select desde base de datos:</div><div><strong>Data source:</strong> db</div><div><strong>Data table / tipo:</strong> departamento</div><div class="mt-2">En la tabla <strong>catalogos</strong> deben existir valores con tipo <strong>departamento</strong>.</div></div>
            <div class="md:col-span-2 flex justify-end"><button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Guardar cambios</button></div>
        </form>
    </div>
@endsection
