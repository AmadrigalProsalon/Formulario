<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<div class="container">
<div class="card">

<form method="POST" action="/guardar" id="multiForm" onsubmit="clearStorage()">
@csrf

<div class="progress">
    <div class="progress-bar" id="progressBar"></div>
</div>

@foreach($fields as $section => $items)

<div class="step {{ $loop->first ? 'active' : '' }}">

<div class="section-title">Sección {{ $section }}</div>

@foreach($items as $f)

<div class="field">

<label>
{{ $f->label }}
@if($f->required)<span class="required">*</span>@endif
</label>

{{-- TEXT --}}
@if($f->type == 'text')
<input type="text" name="{{ $f->name }}" {{ $f->required?'required':'' }}>

{{-- TEXTAREA --}}
@elseif($f->type == 'textarea')
<textarea name="{{ $f->name }}" {{ $f->required?'required':'' }}></textarea>

{{-- SELECT --}}
@elseif($f->type == 'select')
<select name="{{ $f->name }}"
        class="{{ $f->data_source=='db' ? 'dynamic' : '' }}"
        data-type="{{ $f->data_table }}"
        {{ $f->required?'required':'' }}>
<option value="">Selecciona la respuesta</option>
</select>

{{-- RADIO --}}
@elseif($f->type == 'radio')
<div class="options">
@foreach(explode(',', $f->data_source) as $opt)
<label>
<input type="radio" name="{{ $f->name }}" value="{{ trim($opt) }}" {{ $f->required?'required':'' }}>
{{ trim($opt) }}
</label>
@endforeach
</div>

{{-- CHECKBOX --}}
@elseif($f->type == 'checkbox')
<div class="options">
@foreach(explode(',', $f->data_source) as $opt)
<label>
<input type="checkbox" name="{{ $f->name }}[]" value="{{ trim($opt) }}">
{{ trim($opt) }}
</label>
@endforeach
</div>
@endif

</div>

@endforeach

<div class="buttons">

@if(!$loop->first)
<button type="button" class="btn-secondary" onclick="prevStep()">Anterior</button>
@endif

@if(!$loop->last)
<button type="button" class="btn-primary" onclick="nextStep()">Siguiente</button>
@else
<button type="submit" class="btn-primary">Enviar</button>
@endif

</div>

</div>

@endforeach

</form>

</div>
</div>

<script src="{{ asset('js/form.js') }}"></script>
