<link rel="stylesheet" href="/css/style.css">

<div class="container">
  <div class="card">

    <h2>✏️ Editar Campo</h2>

    <form method="POST" action="/admin/fields/update/{{ $field->id }}">
        @csrf

        <div class="field">
            <label>Etiqueta</label>
            <input type="text" name="label" value="{{ $field->label }}" required>
        </div>

        <div class="field">
            <label>Nombre</label>
            <input type="text" name="name" value="{{ $field->name }}" required>
        </div>

        <div class="field">
            <label>Sección</label>
            <input type="text" name="section" value="{{ $field->section }}" required>
        </div>

        <div class="field">
            <label>Tipo</label>
            <select name="type">
                <option value="text" {{ $field->type=='text'?'selected':'' }}>Texto</option>
                <option value="textarea" {{ $field->type=='textarea'?'selected':'' }}>Textarea</option>
                <option value="select" {{ $field->type=='select'?'selected':'' }}>Select</option>
                <option value="radio" {{ $field->type=='radio'?'selected':'' }}>Radio</option>
                <option value="checkbox" {{ $field->type=='checkbox'?'selected':'' }}>Checkbox</option>
            </select>
        </div>

        <div class="field">
            <label>Opciones / Data Source</label>
            <input type="text" name="data_source" value="{{ $field->data_source }}">
        </div>

        <div class="field">
            <label>Tabla (si usa DB)</label>
            <input type="text" name="data_table" value="{{ $field->data_table }}">
        </div>

        <div class="field">
            <label>
                <input type="checkbox" name="required" value="1" {{ $field->required ? 'checked' : '' }}>
                Requerido
            </label>
        </div>

        <button class="btn-primary">Guardar cambios</button>

    </form>

  </div>
</div>
<script>
document.querySelector("select[name='type']").addEventListener("change", function(){

    let source = document.querySelector("input[name='data_source']");

    if (this.value === 'text' || this.value === 'textarea') {
        source.parentElement.style.display = 'none';
    } else {
        source.parentElement.style.display = 'block';
    }

});
</script>
