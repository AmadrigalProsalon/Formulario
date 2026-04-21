<h2>Detalle de Respuesta</h2>

@foreach($data as $key => $value)

<div style="margin-bottom:15px;">
    <strong>{{ $key }}:</strong><br>

    @if(is_array($value))
        {{ implode(', ', $value) }}
    @else
        {{ $value }}
    @endif
</div>

@endforeach
