<h2>Nueva respuesta RH</h2>

@if($formulario)
    <p><strong>Formulario:</strong> {{ $formulario->titulo }}</p>
@endif

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    @foreach($data as $key => $value)
        <tr>
            <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
            <td>
                @if(is_array($value))
                    {{ implode(', ', $value) }}
                @else
                    {{ $value }}
                @endif
            </td>
        </tr>
    @endforeach
</table>
