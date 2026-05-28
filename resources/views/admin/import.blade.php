@extends('admin.layout')

@section('title', 'Importar Excel')
@section('page_title', 'Importar catálogos desde Excel')
@section('page_description', 'Sube un archivo con columnas tipo y valor para alimentar los selects del formulario.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold mb-2">Subir archivo</h2>
            <p class="text-sm text-slate-500 mb-5">El Excel debe tener encabezados llamados <strong>tipo</strong> y <strong>valor</strong>.</p>
            <form method="POST" action="{{ route('admin.import.excel') }}" enctype="multipart/form-data" class="space-y-5">@csrf<div><label class="block text-sm font-semibold mb-2">Archivo Excel</label><input type="file" name="archivo" accept=".xlsx,.xls,.csv" class="w-full rounded-xl border border-slate-300 p-3"></div><button type="submit" class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Importar Excel</button></form>
        </div>
        <div class="bg-slate-950 text-white rounded-2xl shadow-sm p-6"><h3 class="text-lg font-bold mb-3">Formato esperado</h3><div class="bg-white/10 rounded-xl p-4 text-sm font-mono">tipo, valor<br>departamento, Ventas<br>departamento, Almacén<br>horario, Matutino<br>horario, Vespertino</div><a href="{{ route('admin.catalogos.index') }}" class="mt-5 inline-flex rounded-xl bg-white text-slate-950 px-4 py-2 hover:bg-slate-200">Ver catálogos</a></div>
    </div>
@endsection
