<h2>Respuestas</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        @foreach($respuestas as $r)
        <tr>
            <td>{{ $r->id }}</td>
            <td>{{ $r->created_at }}</td>
            <td>
                <a href="/admin/respuesta/{{ $r->id }}">Ver</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $respuestas->links() }}
