@extends('admin.layout')

@section('title', 'Campos del formulario')
@section('page_title', 'Campos del formulario')
@section('page_description', 'Cada formulario tiene sus propios campos dinámicos.')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <form method="GET" action="{{ route('admin.fields.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1">Seleccionar formulario</label><select name="formulario_id" class="w-full rounded-xl border-slate-300" onchange="this.form.submit()">@foreach($formularios as $item)<option value="{{ $item->id }}" @selected($formulario && $formulario->id === $item->id)>{{ $item->titulo }}</option>@endforeach</select></div>
            <div class="flex items-end">@if($formulario)<a href="{{ route('form.show', $formulario) }}" target="_blank" class="w-full text-center rounded-xl bg-blue-600 text-white px-4 py-2.5 hover:bg-blue-700">Ver formulario público</a>@endif</div>
        </form>
    </div>

    @if($formulario)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-bold mb-4">Crear campo para: {{ $formulario->titulo }}</h2>
            <form method="POST" action="{{ route('admin.fields.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <input type="hidden" name="formulario_id" value="{{ $formulario->id }}">
                <div><label class="block text-sm font-semibold mb-1">Nombre interno</label><input type="text" name="name" placeholder="ej. departamento" class="w-full rounded-xl border-slate-300" required><p class="text-xs text-slate-500 mt-1">Sin espacios. Usa guion bajo.</p></div>
                <div><label class="block text-sm font-semibold mb-1">Etiqueta</label><input type="text" name="label" placeholder="ej. Departamento" class="w-full rounded-xl border-slate-300" required></div>
                <div><label class="block text-sm font-semibold mb-1">Sección</label><input type="text" name="section" placeholder="ej. datos_generales" class="w-full rounded-xl border-slate-300" required></div>
                <div><label class="block text-sm font-semibold mb-1">Tipo</label><select name="type" class="w-full rounded-xl border-slate-300" required><option value="text">Texto</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="radio">Radio</option><option value="checkbox">Checkbox</option><option value="email">Email</option><option value="number">Número</option><option value="date">Fecha</option><option value="tel">Teléfono</option></select></div>
                <div><label class="block text-sm font-semibold mb-1">Data source</label><input type="text" name="data_source" placeholder="db, catalogos o valores separados por coma" class="w-full rounded-xl border-slate-300"><p class="text-xs text-slate-500 mt-1">Para BD usa <strong>db</strong> o <strong>catalogos</strong>.</p></div>
                <div><label class="block text-sm font-semibold mb-1">Data table / tipo</label><input type="text" name="data_table" placeholder="ej. departamento" class="w-full rounded-xl border-slate-300"><p class="text-xs text-slate-500 mt-1">Si data_source es db, aquí va el tipo de catálogo.</p></div>
                <div class="flex items-center gap-4 pt-6"><label class="inline-flex items-center gap-2"><input type="checkbox" name="required" value="1" class="rounded"><span>Requerido</span></label><label class="inline-flex items-center gap-2"><input type="checkbox" name="visible" value="1" class="rounded" checked><span>Visible</span></label></div>
                <div class="flex items-end"><button type="submit" class="w-full rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Crear campo</button></div>
            </form>
            <div class="mt-5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 p-4 text-sm"><div class="font-semibold mb-1">Ejemplo correcto para un select desde base de datos:</div><div><strong>Tipo:</strong> select</div><div><strong>Data source:</strong> db</div><div><strong>Data table / tipo:</strong> departamento</div><div class="mt-2">Y en la tabla <strong>catalogos</strong> deben existir registros con <strong>tipo = departamento</strong>.</div></div>
        </div>

        @foreach($fields as $seccion => $campos)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="p-5 border-b border-slate-200"><h2 class="text-lg font-bold">Sección: {{ $seccion }}</h2><p class="text-sm text-slate-500">{{ count($campos) }} campos</p></div>
                <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th class="text-left p-4">Etiqueta</th><th class="text-left p-4">Nombre</th><th class="text-left p-4">Tipo</th><th class="text-left p-4">Data source</th><th class="text-left p-4">Data table</th><th class="text-left p-4">Estado</th><th class="text-right p-4">Acciones</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach($campos as $f)<tr class="hover:bg-slate-50"><td class="p-4 font-semibold">{{ $f->label }}</td><td class="p-4 font-mono text-xs">{{ $f->name }}</td><td class="p-4">{{ $f->type }}</td><td class="p-4">{{ $f->data_source ?: 'Sin source' }}</td><td class="p-4">{{ $f->data_table ?: 'Sin tipo' }}</td><td class="p-4">@if($f->required)<span class="inline-flex rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs">Requerido</span>@endif @if($f->visible)<span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs">Visible</span>@else<span class="inline-flex rounded-full bg-slate-200 text-slate-700 px-3 py-1 text-xs">Oculto</span>@endif</td><td class="p-4 text-right"><div class="flex justify-end gap-2"><a href="{{ route('admin.fields.edit', $f->id) }}" class="rounded-xl bg-slate-200 text-slate-800 px-3 py-2 hover:bg-slate-300">Editar</a><a href="{{ route('admin.fields.toggle', $f->id) }}" class="rounded-xl bg-yellow-500 text-white px-3 py-2 hover:bg-yellow-600">{{ $f->visible ? 'Ocultar' : 'Mostrar' }}</a><form method="POST" action="{{ route('admin.fields.delete', $f->id) }}" onsubmit="return confirm('¿Eliminar este campo?')">@csrf @method('DELETE')<button class="rounded-xl bg-red-600 text-white px-3 py-2 hover:bg-red-700">Eliminar</button></form></div></td></tr>@endforeach</tbody></table></div>
            </div>
        @endforeach
    @endif
@endsection
