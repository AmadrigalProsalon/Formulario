@extends('admin.layout')

@section('title', 'Detalle de respuesta')
@section('page_title', 'Detalle de respuesta #' . $respuesta->id)
@section('page_description', 'Consulta y edita la información enviada en el formulario.')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('admin.respuestas.index') }}"
           class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-300">
            Volver
        </a>

        <div class="text-sm text-slate-500">
            Recibido: {{ $respuesta->created_at?->format('d/m/Y H:i') }}
        </div>
    </div>

    <form method="POST" action="{{ route('admin.respuesta.update', $respuesta->id) }}"
          class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf

        <div class="p-5 border-b border-slate-200">
            <h2 class="text-lg font-bold">Información del formulario</h2>
            <p class="text-sm text-slate-500">Los cambios se guardan directamente en la respuesta.</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($data as $key => $value)
                @php
                    $label = ucfirst(str_replace('_', ' ', $key));
                    $textValue = is_array($value) ? implode(', ', $value) : $value;
                    $isLong = strlen((string) $textValue) > 90;
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-5">
                    <div>
                        <label class="font-semibold text-slate-700">{{ $label }}</label>
                        <div class="text-xs text-slate-400">{{ $key }}</div>
                    </div>

                    <div class="md:col-span-3">
                        @if($isLong)
                            <textarea name="{{ $key }}"
                                      rows="4"
                                      class="w-full rounded-xl border-slate-300">{{ $textValue }}</textarea>
                        @else
                            <input type="text"
                                   name="{{ $key }}"
                                   value="{{ $textValue }}"
                                   class="w-full rounded-xl border-slate-300">
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    Esta respuesta no tiene datos guardados.
                </div>
            @endforelse
        </div>

        <div class="p-5 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="submit" class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">
                Guardar cambios
            </button>
        </div>
    </form>
@endsection
