<h2>Importar Excel</h2>

@if(session('success'))
<p style="color:green">{{ session('success') }}</p>
@endif

<form action="/import-excel" method="POST" enctype="multipart/form-data">
@csrf
<input type="file" name="archivo" required>
<button>Subir Excel</button>
</form>
