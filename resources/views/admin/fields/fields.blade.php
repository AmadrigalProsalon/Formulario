<link rel="stylesheet" href="/css/style.css">

<div class="container">
    <div class="card">

        <div class="header">
            <h2>⚙️ Gestión de Campos</h2>
            <p class="subtitle">Administra dinámicamente el formulario</p>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <!-- ================== FORM ================== -->
        <div class="card form-card">
            <h3>➕ Crear nuevo campo</h3>

            <form method="POST" action="/admin/fields/store" class="form-grid">
                @csrf

                <input type="text" name="label" placeholder="Etiqueta (Ej: Departamento)" required>
                <input type="text" name="name" placeholder="Nombre (ej: departamento)" required>
                <input type="text" name="section" placeholder="Sección (I, II, III...)" required>

                <select name="type" required>
                    <option value="">Tipo</option>
                    <option value="text">Texto</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                </select>

                <input type="text" name="data_source" placeholder="Opciones o 'db'">
                <input type="text" name="data_table" placeholder="Tabla (catalogo, etc)">

                <button class="btn-primary full">Crear campo</button>
            </form>
        </div>

        <!-- ================== LISTA ================== -->
        @foreach($fields as $seccion => $campos)

        <div class="section-card">
            <div class="section-header">
                <h3>Sección {{ $seccion }}</h3>
                <span>{{ count($campos) }} campos</span>
            </div>

            <div class="fields-list">

                @foreach($campos as $f)

                <div class="field-card">

                    <div class="field-info">
                        <strong>{{ $f->label }}</strong>
                        <small>{{ $f->name }}</small>
                    </div>

                    <div class="badges">
                        <span class="badge type">{{ $f->type }}</span>

                        @if($f->required)
                            <span class="badge required">Requerido</span>
                        @endif

                        @if($f->visible)
                            <span class="badge visible">Visible</span>
                        @else
                            <span class="badge hidden">Oculto</span>
                        @endif
                    </div>

                    <div class="actions">
                        <a href="/admin/fields/edit/{{ $f->id }}" class="btn-warning">Editar</a>

                        <a href="/admin/fields/toggle/{{ $f->id }}" class="btn-secondary">
                            {{ $f->visible ? 'Ocultar' : 'Mostrar' }}
                        </a>

                        <form method="POST" action="/admin/fields/delete/{{ $f->id }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger">Eliminar</button>
                        </form>
                    </div>

                </div>

                @endforeach

            </div>
        </div>

        @endforeach

    </div>
</div>
