@extends('admin.layout')

@section('title', 'Solicitudes')
@section('page_title', 'Vacaciones y permisos')
@section('page_description', 'Consulta todas las solicitudes registradas y sus fechas autorizadas.')

@section('content')
@php
    $total = \App\Models\PermisoSolicitud::count();
    $aprobadas = \App\Models\PermisoSolicitud::where(function ($q) {
        $q->where('formato_recibido', 1)->orWhere('estatus', 'formato_recibido');
    })->count();
    $vacaciones = \App\Models\PermisoSolicitud::whereHas('tipoPermiso', fn ($q) => $q->where('slug', 'vacaciones'))->count();
    $permisos = max(0, $total - $vacaciones);
@endphp

<div class="mb-7 rounded-[2rem] bg-gradient-to-br from-slate-950 via-indigo-950 to-violet-900 p-7 md:p-9 text-white shadow-xl shadow-indigo-950/10 overflow-hidden relative">
    <div class="absolute -top-16 -right-12 h-56 w-56 rounded-full bg-violet-400/20 blur-3xl"></div>
    <div class="absolute -bottom-20 right-40 h-48 w-48 rounded-full bg-cyan-300/10 blur-3xl"></div>
    <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-[.22em] text-indigo-200">Recursos Humanos</div>
            <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">Solicitudes registradas</h1>
            <p class="mt-2 max-w-2xl text-sm text-indigo-100 md:text-base">
                Las solicitudes existentes se muestran como aprobadas. Las fechas salteadas conservan únicamente los días realmente solicitados.
            </p>
        </div>
        <a href="{{ url('/permisos/solicitud') }}" target="_blank"
           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 font-bold text-indigo-950 shadow-lg transition hover:-translate-y-0.5 hover:bg-indigo-50">
            <span class="text-xl">＋</span> Nueva solicitud
        </a>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</div>
        <div class="mt-2 text-3xl font-black text-slate-950">{{ $total }}</div>
    </div>
    <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Aprobadas</div>
        <div class="mt-2 text-3xl font-black text-emerald-700">{{ $aprobadas }}</div>
    </div>
    <div class="rounded-3xl border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-wider text-violet-600">Vacaciones</div>
        <div class="mt-2 text-3xl font-black text-violet-700">{{ $vacaciones }}</div>
    </div>
    <div class="rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Otros permisos</div>
        <div class="mt-2 text-3xl font-black text-blue-700">{{ $permisos }}</div>
    </div>
</div>

<div class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <form method="GET" action="{{ route('admin.permisos.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Colaborador</label>
            <input type="text" name="q" value="{{ request('q') }}"
                   class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                   placeholder="Nombre, correo o número">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Departamento</label>
            <select name="area_id" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                <option value="">Todos</option>
                @foreach(($areas ?? collect()) as $area)
                    <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Tipo</label>
            <select name="tipo_permiso_id" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                <option value="">Todos</option>
                @foreach(($tiposPermisos ?? $tipos ?? collect()) as $tipo)
                    <option value="{{ $tipo->id }}" @selected(request('tipo_permiso_id') == $tipo->id)>{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Estatus</label>
            <select name="estatus" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                <option value="">Todos</option>
                <option value="formato_recibido" @selected(request('estatus') === 'formato_recibido')>Aprobada</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button class="flex-1 rounded-xl bg-slate-950 px-4 py-2.5 font-bold text-white hover:bg-slate-800">Filtrar</button>
            <a href="{{ route('admin.permisos.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 font-semibold text-slate-600 hover:bg-slate-200">Limpiar</a>
        </div>
    </form>
</div>

<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[920px] text-sm">
            <thead class="bg-slate-50/90 text-slate-500">
                <tr>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Folio</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Colaborador</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Tipo</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Días autorizados</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Estatus</th>
                    <th class="p-4 text-right text-xs font-bold uppercase tracking-wider">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($solicitudes as $s)
                    @php
                        $fechasIndividuales = $s->dias ?? collect();
                        $esHistorica = str_contains(mb_strtolower((string) $s->motivo), 'históric')
                            || str_contains(mb_strtolower((string) $s->motivo), 'histor');
                    @endphp
                    <tr class="transition hover:bg-violet-50/30">
                        <td class="p-4">
                            <div class="font-black text-slate-950">#{{ $s->id }}</div>
                            <div class="text-xs text-slate-400">{{ $s->created_at?->format('d/m/Y') }}</div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900">{{ $s->empleado?->nombre }}</div>
                            <div class="mt-0.5 text-xs text-slate-500">{{ $s->area?->nombre ?? $s->empleado?->area?->nombre }}</div>
                            <div class="text-xs text-violet-600">Líder: {{ $s->lider?->nombre ?? $s->empleado?->lider?->nombre ?? 'Sin líder' }}</div>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">
                                {{ $s->tipoPermiso?->nombre }}
                            </span>
                            @if($esHistorica)
                                <div class="mt-1 text-[11px] text-slate-400">Registro histórico</div>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900">{{ number_format((float) $s->dias_solicitados, 0) }} día(s)</div>
                            @if($fechasIndividuales->count())
                                <div class="mt-1 flex max-w-sm flex-wrap gap-1">
                                    @foreach($fechasIndividuales->take(4) as $dia)
                                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] text-slate-600">{{ $dia->fecha?->format('d/m/Y') }}</span>
                                    @endforeach
                                    @if($fechasIndividuales->count() > 4)
                                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] text-slate-600">+{{ $fechasIndividuales->count() - 4 }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="text-xs text-slate-500">{{ $s->fecha_inicio?->format('d/m/Y') }}{{ $s->fecha_fin && $s->fecha_fin->ne($s->fecha_inicio) ? ' al '.$s->fecha_fin->format('d/m/Y') : '' }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-700">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Aprobada
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.permisos.show', $s) }}"
                               class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-700 shadow-sm hover:border-violet-300 hover:text-violet-700">
                                Ver solicitud
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-12 text-center text-slate-500">No hay solicitudes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 p-5">{{ $solicitudes->links() }}</div>
</div>
@endsection
