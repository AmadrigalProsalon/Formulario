@extends('admin.layout')

@section('title', 'Calendario de ausencias')
@section('page_title', 'Calendario de ausencias')
@section('page_description', 'Consulta vacaciones, permisos y ausencias por mes, área, tipo y estado.')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-sm text-slate-500">Mes consultado</p>
                <p class="text-2xl font-bold text-slate-900">
                    {{ ucfirst($fechaBase->translatedFormat('F Y')) }}
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-sm text-slate-500">Solicitudes visibles</p>
                <p class="text-2xl font-bold text-slate-900">{{ $totalSolicitudes }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-sm text-slate-500">Inicio del mes</p>
                <p class="text-2xl font-bold text-slate-900">{{ $inicioMes->format('d/m/Y') }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-sm text-slate-500">Fin del mes</p>
                <p class="text-2xl font-bold text-slate-900">{{ $finMes->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('admin.permisos.calendario') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mes</label>
                    <select name="mes" class="w-full rounded-xl border-slate-300">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected((int) request('mes', $fechaBase->month) === $m)>
                                {{ ucfirst(\Carbon\Carbon::create($fechaBase->year, $m, 1)->translatedFormat('F')) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Año</label>
                    <input type="number" name="anio" value="{{ request('anio', $fechaBase->year) }}" class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Área</label>
                    <select name="area_id" class="w-full rounded-xl border-slate-300">
                        <option value="">Todas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" @selected((string) request('area_id') === (string) $area->id)>
                                {{ $area->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                    <select name="tipo_permiso_id" class="w-full rounded-xl border-slate-300">
                        <option value="">Todos</option>
                        @foreach($tiposPermiso as $tipo)
                            <option value="{{ $tipo->id }}" @selected((string) request('tipo_permiso_id') === (string) $tipo->id)>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                    <select name="estatus" class="w-full rounded-xl border-slate-300">
                        <option value="">Todos</option>
                        @foreach($estatusOpciones as $value => $label)
                            <option value="{{ $value }}" @selected(request('estatus') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button class="flex-1 rounded-xl bg-slate-950 text-white px-4 py-2.5 hover:bg-slate-800">
                        Filtrar
                    </button>
                    <a href="{{ route('admin.permisos.calendario') }}" class="rounded-xl bg-slate-100 text-slate-700 px-4 py-2.5 hover:bg-slate-200">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ ucfirst($fechaBase->translatedFormat('F Y')) }}</h2>
                    <p class="text-sm text-slate-500">Vista mensual de ausencias registradas.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.permisos.calendario', array_merge(request()->except(['mes', 'anio']), ['mes' => $prev->month, 'anio' => $prev->year])) }}"
                       class="rounded-xl bg-slate-100 text-slate-700 px-4 py-2 hover:bg-slate-200">
                        ← Mes anterior
                    </a>
                    <a href="{{ route('admin.permisos.calendario') }}"
                       class="rounded-xl bg-slate-950 text-white px-4 py-2 hover:bg-slate-800">
                        Hoy
                    </a>
                    <a href="{{ route('admin.permisos.calendario', array_merge(request()->except(['mes', 'anio']), ['mes' => $next->month, 'anio' => $next->year])) }}"
                       class="rounded-xl bg-slate-100 text-slate-700 px-4 py-2 hover:bg-slate-200">
                        Mes siguiente →
                    </a>
                </div>
            </div>

            <div class="hidden md:grid grid-cols-7 bg-slate-50 border-b border-slate-200 text-sm font-semibold text-slate-600">
                <div class="p-3 text-center">Lunes</div>
                <div class="p-3 text-center">Martes</div>
                <div class="p-3 text-center">Miércoles</div>
                <div class="p-3 text-center">Jueves</div>
                <div class="p-3 text-center">Viernes</div>
                <div class="p-3 text-center">Sábado</div>
                <div class="p-3 text-center">Domingo</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-7">
                @foreach($dias as $dia)
                    @php
                        $fecha = $dia['fecha'];
                        $esHoy = $fecha->isSameDay(now());
                    @endphp

                    <div class="min-h-36 border-b md:border-r border-slate-100 p-3 {{ $dia['en_mes'] ? 'bg-white' : 'bg-slate-50 text-slate-400' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-bold {{ $esHoy ? 'bg-slate-950 text-white rounded-full w-8 h-8 flex items-center justify-center' : '' }}">
                                {{ $fecha->day }}
                            </div>
                            <div class="md:hidden text-xs text-slate-500">
                                {{ ucfirst($fecha->translatedFormat('l')) }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            @forelse($dia['eventos'] as $evento)
                                <div class="rounded-xl border px-3 py-2 {{ $evento->clase_estado }}">
                                    <div class="font-semibold text-xs leading-tight">
                                        {{ $evento->empleado_nombre ?? 'Empleado sin nombre' }}
                                    </div>
                                    <div class="text-[11px] leading-tight opacity-80 mt-1">
                                        {{ $evento->tipo_permiso_nombre ?? 'Permiso' }} · {{ $evento->etiqueta_estado }}
                                    </div>
                                    <div class="text-[11px] leading-tight opacity-70 mt-1">
                                        {{ $evento->area_nombre ?? 'Sin área' }}
                                    </div>
                                </div>
                            @empty
                                <div class="hidden md:block text-xs text-slate-300">Sin ausencias</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
