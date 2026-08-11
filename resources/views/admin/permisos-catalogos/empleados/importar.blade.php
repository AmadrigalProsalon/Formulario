@extends('admin.layout')

@section('title', 'Importar empleados y vacaciones')
@section('page_title', 'Importar empleados y vacaciones')
@section('page_description', 'Carga empleados, líderes, días ganados acumulados y vacaciones históricas desde CSV o Excel.')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.permisos.empleados.index') }}" class="rounded-xl bg-slate-200 text-slate-800 px-4 py-2 hover:bg-slate-300">Volver a empleados</a>
        @if(Route::has('admin.ausencias.calendario'))
            <a href="{{ route('admin.ausencias.calendario') }}" class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">Ver calendario</a>
        @endif
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 p-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('resultado_importacion'))
        @php($r = session('resultado_importacion'))
        <div class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 p-5">
            <div class="font-bold mb-3 text-lg">Importación terminada</div>
            @if(!empty($r['fecha_corte']))<div class="mb-4 text-sm">Fecha de corte aplicada: <strong>{{ $r['fecha_corte'] }}</strong></div>@endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Empleados creados</div>
                    <div class="text-2xl font-bold">{{ $r['creados'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Empleados actualizados</div>
                    <div class="text-2xl font-bold">{{ $r['actualizados'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Áreas creadas</div>
                    <div class="text-2xl font-bold">{{ $r['areas_creadas'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Líderes creados</div>
                    <div class="text-2xl font-bold">{{ $r['lideres_creados'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Vacaciones históricas importadas</div>
                    <div class="text-2xl font-bold">{{ $r['vacaciones_creadas'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Históricos reemplazados</div>
                    <div class="text-2xl font-bold">{{ $r['vacaciones_reemplazadas'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Vacaciones omitidas</div>
                    <div class="text-2xl font-bold">{{ $r['vacaciones_omitidas'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-3 border border-emerald-100">
                    <div class="text-slate-500">Filas omitidas</div>
                    <div class="text-2xl font-bold">{{ $r['omitidos'] ?? 0 }}</div>
                </div>
            </div>

            @if(!empty($r['errores']))
                <div class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-4">
                    <div class="font-semibold mb-2">Avisos</div>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($r['errores'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 max-w-4xl">
        <h2 class="text-xl font-bold mb-2">Subir archivo de saldos de vacaciones</h2>
        <p class="text-sm text-slate-500 mb-5">
            Puedes subir el CSV/Excel de vacaciones real. El sistema actualiza empleados existentes por <strong>CLAVE</strong>, crea empleados nuevos,
            crea áreas/líderes y registra las fechas de vacaciones históricas para que aparezcan en el calendario.
        </p>

        <form method="POST" action="{{ route('admin.permisos.empleados.importar.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">Archivo</label>
                <input type="file" name="archivo" accept=".csv,.txt,.xlsx,.xls" class="w-full rounded-xl border border-slate-300 p-3" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Fecha de corte del archivo</label>
                    <input type="date" name="fecha_corte" value="{{ old('fecha_corte', now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border border-slate-300 p-3" required>
                    <p class="mt-1 text-xs text-slate-500">Usa el último día hasta el que RH actualizó la columna de días ganados.</p>
                </div>
                <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm text-violet-900">
                    <strong>Cálculo automático</strong>
                    <p class="mt-1">Desde esta fecha, el sistema genera el proporcional diario hasta hoy y considera cambios de antigüedad.</p>
                </div>
            </div>

            <button class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">
                Importar empleados y vacaciones
            </button>
        </form>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 p-5">
                <div class="font-bold mb-2">Columnas principales detectadas</div>
                <div class="font-mono text-xs bg-white rounded-xl p-3 overflow-x-auto">
                    CLAVE; NOMBRE; DEPARTAMENTO; PUESTO; JEFE DIRECTO; FECHA INGRESO; DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL
                </div>
            </div>

            <div class="rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 p-5">
                <div class="font-bold mb-2">Regla para columnas sin encabezado</div>
                <p>
                    Las columnas sin título que contienen fechas se importan como <strong>vacaciones históricas ya tomadas</strong>.
                    Estas fechas cuentan como días ya usados. La base ganada se toma exclusivamente de <strong>DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL</strong>.
                </p>
            </div>
        </div>
    </div>
@endsection
