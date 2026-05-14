<link rel="stylesheet" href="/css/style.css">

<div class="container">
<div class="card">

<h2>Detalle de Respuesta</h2>
<p class="subtitle">Puedes editar los campos directamente</p>

<form method="POST" action="/admin/respuesta/update/{{ request()->route('id') }}">
@csrf

<table class="edit-table">
    <thead>
        <tr>
            <th>Campo</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>

        @foreach($data as $key => $value)
        <tr>
            <td class="field-name">
                {{ ucfirst(str_replace('_',' ', $key)) }}
            </td>

            <td>
                @if(is_array($value))
                    <input type="text" name="{{ $key }}" value="{{ implode(', ', $value) }}">
                @else
                    <input type="text" name="{{ $key }}" value="{{ $value }}">
                @endif
            </td>
        </tr>
        @endforeach

    </tbody>
</table>

<button class="btn-primary">💾 Guardar cambios</button>

</form>

</div>
</div>
