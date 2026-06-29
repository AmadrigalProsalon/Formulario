@extends('admin.layout')

@section('title', 'Calendario de ausencias')
@section('page_title', 'Calendario de ausencias')
@section('page_description', 'Vista mensual de vacaciones, permisos con goce, permisos sin goce y otras ausencias.')

@section('content')
@php
    $anio = isset($anio) ? (int) $anio : (int) request('anio', now()->year);
    $mes = isset($mes) ? (int) $mes : (int) request('mes', now()->month);

    $fechaBase = $fechaBase ?? \Carbon\Carbon::create($anio, $mes, 1)->locale('es')->startOfMonth();
    $diasCalendario = $diasCalendario ?? collect(\Carbon\CarbonPeriod::create(
        $fechaBase->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY),
        $fechaBase->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY)
    ));

    $solicitudesPorDia = $solicitudesPorDia ?? collect();

    $nombresMeses = $nombresMeses ?? [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    $diasSemana = $diasSemana ?? ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    $mesAnterior = $fechaBase->copy()->subMonth();
    $mesSiguiente = $fechaBase->copy()->addMonth();

    function claseEstatusCalendario($estatus) {
        return match($estatus) {
            'formato_recibido' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'formato_pendiente' => 'bg-amber-100 text-amber-800 border-amber-200',
            'con_observaciones' => 'bg-orange-100 text-orange-800 border-orange-200',
            'cancelado' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-blue-100 text-blue-800 border-blue-200',
        };
    }

    function textoEstatusCalendario($estatus) {
        return match($estatus) {
            'formato_recibido' => 'Recibido',
            'formato_pendiente' => 'Pendiente',
            'con_observaciones' => 'Observaciones',
            'cancelado' => 'Cancelado',
            'formato_enviado' => 'Enviado',
            default => ucfirst(str_replace('_', ' ', (string) $estatus)),
        };
    }
@endphp

<div class="space-y-6">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">
                    {{ ucfirst($fechaBase->locale('es')->translatedFormat('F Y')) }}
                </h2>
                <p class="text-sm text-slate-500">
                    Consulta las ausencias registradas por día.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.permisos.calendario', array_merge(request()->except(['mes', 'anio']), ['mes' => $mesAnterior->month, 'anio' => $mesAnterior->year])) }}"
                   class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">
                    Mes anterior
                </a>

                <a href="{{ route('admin.permisos.calendario') }}"
                   class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">
                    Hoy
                </a>

                <a href="{{ route('admin.permisos.calendario', array_merge(request()->except(['mes', 'anio']), ['mes' => $mesSiguiente->month, 'anio' => $mesSiguiente->year])) }}"
                   class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">
                    Mes siguiente
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.permisos.calendario') }}" class="mt-5 grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Mes</label>
                <select name="mes" class="w-full rounded-xl border-slate-300">
                    @foreach($nombresMeses as $numero => $nombre)
                        <option value="{{ $numero }}" @selected((int) request('mes', $mes) === (int) $numero)>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Año</label>
                <input type="number"
                       name="anio"
                       value="{{ request('anio', $anio) }}"
                       class="w-full rounded-xl border-slate-300">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Área</label>
                <select name="area_id" class="w-full rounded-xl border-slate-300">
                    <option value="">Todas</option>
                    @foreach(($areas ?? collect()) as $area)
                        <option value="{{ $area->id }}" @selected((string) request('area_id') === (string) $area->id)>
                            {{ $area->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Tipo</label>
                <select name="tipo_permiso_id" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    @foreach(($tipos ?? collect()) as $tipo)
                        <option value="{{ $tipo->id }}" @selected((string) request('tipo_permiso_id') === (string) $tipo->id)>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Estatus</label>
                <select name="estatus" class="w-full rounded-xl border-slate-300">
                    <option value="">Todos</option>
                    @foreach(($estatusOptions ?? []) as $key => $label)
                        <option value="{{ $key }}" @selected(request('estatus') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-5 flex justify-end gap-2">
                <a href="{{ route('admin.permisos.calendario') }}"
                   class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">
                    Limpiar
                </a>

                <button type="submit"
                        class="px-5 py-2 rounded-xl bg-slate-950 text-white hover:bg-slate-800">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-7 bg-slate-950 text-white text-sm font-semibold">
            @foreach($diasSemana as $diaSemana)
                <div class="p-3 text-center">
                    {{ $diaSemana }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @foreach($diasCalendario as $dia)
                @php
                    $key = $dia->format('Y-m-d');
                    $solicitudesDia = $solicitudesPorDia->get($key, collect());
                    $esMesActual = $dia->month === $fechaBase->month;
                    $esHoy = $dia->isToday();
                @endphp

                <div class="min-h-[150px] border-r border-b border-slate-100 p-2 {{ $esMesActual ? 'bg-white' : 'bg-slate-50' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                            {{ $esHoy ? 'bg-slate-950 text-white' : ($esMesActual ? 'text-slate-800' : 'text-slate-400') }}">
                            {{ $dia->day }}
                        </span>

                        @if($solicitudesDia->count())
                            <span class="text-xs rounded-full bg-slate-100 text-slate-600 px-2 py-1">
                                {{ $solicitudesDia->count() }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-1">
                        @forelse($solicitudesDia->take(4) as $solicitud)
                            <div class="rounded-lg border px-2 py-1 text-xs {{ claseEstatusCalendario($solicitud->estatus ?? '') }}">
                                <div class="font-bold truncate">
                                    {{ $solicitud->empleado_nombre ?? 'Empleado' }}
                                </div>
                                <div class="truncate">
                                    {{ $solicitud->tipo_permiso_nombre ?? 'Permiso' }}
                                </div>
                                <div class="text-[10px] opacity-80">
                                    {{ textoEstatusCalendario($solicitud->estatus ?? '') }}
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-slate-300 mt-6 text-center">
                                Sin ausencias
                            </div>
                        @endforelse

                        @if($solicitudesDia->count() > 4)
                            <div class="text-xs text-slate-500 text-center pt-1">
                                +{{ $solicitudesDia->count() - 4 }} más
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <h3 class="font-bold text-slate-900 mb-3">Leyenda</h3>

        <div class="flex flex-wrap gap-2 text-sm">
            <span class="px-3 py-1 rounded-full border bg-blue-100 text-blue-800 border-blue-200">Enviado</span>
            <span class="px-3 py-1 rounded-full border bg-amber-100 text-amber-800 border-amber-200">Pendiente</span>
            <span class="px-3 py-1 rounded-full border bg-emerald-100 text-emerald-800 border-emerald-200">Recibido</span>
            <span class="px-3 py-1 rounded-full border bg-orange-100 text-orange-800 border-orange-200">Observaciones</span>
            <span class="px-3 py-1 rounded-full border bg-rose-100 text-rose-800 border-rose-200">Cancelado</span>
        </div>
    </div>
</div>
@endsection
