<h2>Campos del formulario</h2>

<form method="POST" action="/admin/fields/store">
@csrf

<input name="label" placeholder="Label">
<input name="name" placeholder="name">
<input name="section" placeholder="I, II, III...">

<select name="type">
    <option>text</option>
    <option>textarea</option>
    <option>select</option>
    <option>radio</option>
    <option>checkbox</option>
</select>

<input name="data_source" placeholder="Opciones o db">
<input name="data_table" placeholder="departamento, puesto...">

<button>Crear</button>

</form>

<hr>

@foreach($fields as $f)

<div style="border:1px solid #ddd; padding:10px; margin:10px 0;">
    {{ $f->label }} ({{ $f->type }})

    <form method="POST" action="/admin/fields/delete/{{ $f->id }}">
        @csrf
        @method('DELETE')
        <button>Eliminar</button>
    </form>
</div>

@endforeach
