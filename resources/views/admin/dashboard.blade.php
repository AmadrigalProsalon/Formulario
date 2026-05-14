<link rel="stylesheet" href="/css/style.css">

<div class="container">
<div class="card">

<h2>Respuestas</h2>
<p class="subtitle">Consulta y filtra las respuestas del formulario</p>

<!--FILTROS -->
<div class="filters">
    <input type="text" id="searchInput" placeholder="Buscar..." />

    <select id="filterDate">
        <option value="">Todas las fechas</option>
        <option value="today">Hoy</option>
        <option value="week">Última semana</option>
    </select>
</div>

<!--TABLA -->
<div class="table-container">
<table id="tableRespuestas">
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
            <td>#{{ $r->id }}</td>
            <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
            <td>
                <a href="/admin/respuesta/{{ $r->id }}" class="btn-view">Ver</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

<!-- PAGINACIÓN -->
<div class="pagination">
{{ $respuestas->links() }}
</div>

</div>
</div>

<script>
// =======================
//BUSCADOR
// =======================
document.getElementById("searchInput").addEventListener("keyup", function() {

    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tableRespuestas tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });

});

// =======================
// FILTRO FECHA
// =======================
document.getElementById("filterDate").addEventListener("change", function() {

    let value = this.value;
    let rows = document.querySelectorAll("#tableRespuestas tbody tr");

    let now = new Date();

    rows.forEach(row => {

        let dateText = row.children[1].innerText;
        let parts = dateText.split(/[\/ :]/); // dd/mm/yyyy

        let rowDate = new Date(parts[2], parts[1]-1, parts[0]);

        let show = true;

        if (value === "today") {
            show = rowDate.toDateString() === now.toDateString();
        }

        if (value === "week") {
            let diff = (now - rowDate) / (1000 * 60 * 60 * 24);
            show = diff <= 7;
        }

        row.style.display = show ? "" : "none";

    });

});
</script>
