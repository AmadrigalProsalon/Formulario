<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $formulario->titulo }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold">{{ $formulario->titulo }}</h1>

                    @if($formulario->descripcion)
                        <p class="text-slate-500 mt-2">{{ $formulario->descripcion }}</p>
                    @endif
                </div>

                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.formularios.index') }}"
                           class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">
                            Editar formulario
                        </a>

                        <a href="{{ route('admin.fields.index', ['formulario_id' => $formulario->id]) }}"
                           class="rounded-xl bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
                            Editar campos
                        </a>

                        <a href="{{ route('admin.respuestas.index', ['formulario_id' => $formulario->id]) }}"
                           class="rounded-xl bg-green-600 text-white px-4 py-2 hover:bg-green-700">
                            Ver respuestas
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if(session('error'))
            <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                <div class="font-semibold mb-2">Revisa estos campos:</div>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($fields->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center text-slate-500">
                Este formulario todavía no tiene campos configurados.

                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="mt-5">
                        <a href="{{ route('admin.fields.index', ['formulario_id' => $formulario->id]) }}"
                           class="inline-flex rounded-xl bg-blue-600 text-white px-5 py-2.5 hover:bg-blue-700">
                            Agregar campos
                        </a>
                    </div>
                @endif
            </div>
        @else
            <form method="POST" action="{{ route('form.store', $formulario) }}" class="space-y-6">
                @csrf

                @foreach($fields as $section => $items)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-5 border-b border-slate-200 bg-slate-50">
                            <h2 class="text-xl font-bold">
                                {{ ucfirst(str_replace('_', ' ', $section)) }}
                            </h2>
                        </div>

                        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                            @foreach($items as $f)
                                @php
                                    $label = $f->label;
                                    $name = $f->name;
                                    $required = $f->required ? 'required' : '';
                                    $oldValue = old($name);

                                    $source = strtolower(trim($f->data_source ?? ''));

                                    if (in_array($source, ['catalogos', 'db', 'database'])) {
                                        $options = $catalogos[$f->data_table] ?? [];
                                    } else {
                                        $options = collect(explode(',', $f->data_source ?? ''))
                                            ->map(fn($item) => trim($item))
                                            ->filter()
                                            ->values()
                                            ->toArray();
                                    }
                                @endphp

                                <div class="{{ $f->type === 'textarea' ? 'md:col-span-2' : '' }}">
                                    <label class="block text-sm font-semibold mb-1">
                                        {{ $label }}

                                        @if($f->required)
                                            <span class="text-red-600">*</span>
                                        @endif

                                        @if(auth()->check() && auth()->user()->is_admin)
                                            <a href="{{ route('admin.fields.edit', $f->id) }}"
                                               class="text-xs text-blue-600 underline ml-2">
                                                Editar campo
                                            </a>
                                        @endif
                                    </label>

                                    @if($f->type === 'textarea')
                                        <textarea name="{{ $name }}"
                                                  rows="4"
                                                  class="w-full rounded-xl border-slate-300"
                                                  {{ $required }}>{{ $oldValue }}</textarea>

                                    @elseif($f->type === 'select')
                                        <select name="{{ $name }}"
                                                class="w-full rounded-xl border-slate-300"
                                                {{ $required }}>
                                            <option value="">Selecciona una opción</option>

                                            @foreach($options as $option)
                                                <option value="{{ $option }}" @selected($oldValue == $option)>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @if(empty($options))
                                            <p class="text-xs text-red-600 mt-1">
                                                Este select no tiene opciones. Revisa data_source y data_table en el admin.
                                            </p>
                                        @endif

                                    @elseif($f->type === 'radio')
                                        @if(empty($options))
                                            <p class="text-xs text-red-600">
                                                Este radio no tiene opciones. Revisa data_source y data_table en el admin.
                                            </p>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($options as $option)
                                                    <label class="flex items-center gap-2">
                                                        <input type="radio"
                                                               name="{{ $name }}"
                                                               value="{{ $option }}"
                                                               @checked($oldValue == $option)
                                                               {{ $required }}>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif

                                    @elseif($f->type === 'checkbox')
                                        @if(empty($options))
                                            <p class="text-xs text-red-600">
                                                Este checkbox no tiene opciones. Revisa data_source y data_table en el admin.
                                            </p>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($options as $option)
                                                    <label class="flex items-center gap-2">
                                                        <input type="checkbox"
                                                               name="{{ $name }}[]"
                                                               value="{{ $option }}"
                                                               @checked(is_array($oldValue) && in_array($option, $oldValue))>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif

                                    @elseif($f->type === 'email')
                                        <input type="email"
                                               name="{{ $name }}"
                                               value="{{ $oldValue }}"
                                               class="w-full rounded-xl border-slate-300"
                                               {{ $required }}>

                                    @elseif($f->type === 'number')
                                        <input type="number"
                                               name="{{ $name }}"
                                               value="{{ $oldValue }}"
                                               class="w-full rounded-xl border-slate-300"
                                               {{ $required }}>

                                    @elseif($f->type === 'date')
                                        <input type="date"
                                               name="{{ $name }}"
                                               value="{{ $oldValue }}"
                                               class="w-full rounded-xl border-slate-300"
                                               {{ $required }}>

                                    @elseif($f->type === 'tel')
                                        <input type="tel"
                                               name="{{ $name }}"
                                               value="{{ $oldValue }}"
                                               class="w-full rounded-xl border-slate-300"
                                               {{ $required }}>

                                    @else
                                        <input type="text"
                                               name="{{ $name }}"
                                               value="{{ $oldValue }}"
                                               class="w-full rounded-xl border-slate-300"
                                               {{ $required }}>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-xl bg-slate-950 text-white px-8 py-3 hover:bg-slate-800">
                        Enviar formulario
                    </button>
                </div>
            </form>
        @endif
    </div>
</body>
</html>
