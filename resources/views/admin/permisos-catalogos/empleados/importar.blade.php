@extends('admin.layout')

@section('title', 'Importar empleados')
@section('page_title', 'Importar empleados')
@section('page_description', 'Carga empleados, áreas y líderes desde CSV o Excel.')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.permisos.empleados.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-300">Volver</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 max-w-3xl">
        <h2 class="text-xl font-bold mb-2">Subir archivo</h2>
        <p class="text-sm text-slate-500 mb-5">Formatos aceptados: CSV, XLSX o XLS. El sistema crea áreas y líderes si no existen.</p>

        <form method="POST" action="{{ route('admin.permisos.empleados.importar.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">Archivo</label>
                <input type="file" name="archivo" accept=".csv,.txt,.xlsx,.xls" class="w-full rounded-xl border border-slate-300 p-3" required>
            </div>
            <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Importar empleados</button>
        </form>

        <div class="mt-6 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 p-5 text-sm">
            <div class="font-bold mb-2">Columnas sugeridas</div>
            <div class="font-mono text-xs bg-white rounded-xl p-3 overflow-x-auto">
                numero_empleado, nombre, correo, area, puesto, lider, correo_lider, fecha_ingreso, activo
            </div>
            <p class="mt-3">También acepta columnas como <strong>departamento</strong>, <strong>trabajador</strong>, <strong>colaborador</strong>, <strong>jefe</strong> o <strong>email</strong>.</p>
        </div>
    </div>
@endsection
