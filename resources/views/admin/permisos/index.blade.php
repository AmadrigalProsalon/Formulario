@extends('admin.layout')

@section('title', 'Solicitudes')
@section('page_title', 'Vacaciones y permisos')
@section('page_description', 'Consulta solicitudes pendientes, aprobadas, rechazadas e históricas.')

@section('content')
@php
    $total = \App\Models\PermisoSolicitud::count();
    $aprobadas = \App\Models\PermisoSolicitud::where('estatus', 'formato_recibido')->where('formato_recibido', 1)->count();
    $pendientes = \App\Models\PermisoSolicitud::whereIn('estatus', ['formato_generado', 'formato_enviado', 'formato_pendiente', 'pendiente_firma_colaborador', 'con_observaciones'])->count();
    $rechazadas = \App\Models\PermisoSolicitud::where('estatus', 'rechazado')->count();
@endphp

<div class="relative mb-7 overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-indigo-950 to-violet-900 p-7 text-white shadow-xl shadow-indigo-950/10 md:p-9">
    <div class="absolute -right-12 -top-16 h-56 w-56 rounded-full bg-violet-400/20 blur-3xl"></div>
    <div class="absolute -bottom-20 right-40 h-48 w-48 rounded-full bg-cyan-300/10 blur-3xl"></div>
    <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-[.22em] text-indigo-200">Recursos Humanos</div>
            <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">Solicitudes registradas</h1>
            <p class="mt-2 max-w-2xl text-sm text-indigo-100 md:text-base">
                Las solicitudes nuevas permanecen pendientes hasta que RH las aprueba o rechaza. El formato firmado es opcional y puede adjuntarse como soporte.
            </p>
        </div>
        <a href="{{ url('/permisos/solicitud') }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 font-bold text-indigo-950 shadow-lg hover:bg-indigo-50">
            + Nueva solicitud
        </a>
    </div>
</div>

<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</div><div class="mt-2 text-3xl font-black text-slate-950">{{ $total }}</div></div>
    <div class="rounded-3xl border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-violet-600">Pendientes</div><div class="mt-2 text-3xl font-black text-violet-700">{{ $pendientes }}</div></div>
    <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Aprobadas</div><div class="mt-2 text-3xl font-black text-emerald-700">{{ $aprobadas }}</div></div>
    <div class="rounded-3xl border border-rose-200 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-rose-600">Rechazadas</div><div class="mt-2 text-3xl font-black text-rose-700">{{ $rechazadas }}</div></div>
</div>

<div class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <form method="GET" action="{{ route('admin.permisos.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Colaborador</label>
            <input type="text" name="q" value="{{ request('q') }}" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500" placeholder="Nombre, correo o número">
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
                @foreach(($tiposPermisos ?? collect()) as $tipo)
                    <option value="{{ $tipo->id }}" @selected(request('tipo_permiso_id') == $tipo->id)>{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Estatus</label>
            <select name="estatus" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                <option value="">Todos</option>
                <option value="pendiente" @selected(request('estatus') === 'pendiente')>Pendientes</option>
                <option value="con_observaciones" @selected(request('estatus') === 'con_observaciones')>Con observaciones</option>
                <option value="formato_recibido" @selected(request('estatus') === 'formato_recibido')>Aprobada</option>
                <option value="rechazado" @selected(request('estatus') === 'rechazado')>Rechazada</option>
                <option value="cancelado" @selected(request('estatus') === 'cancelado')>Cancelada</option>
                <option value="historico" @selected(request('estatus') === 'historico')>Registro histórico</option>
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
        <table class="w-full min-w-[980px] text-sm">
            <thead class="bg-slate-50/90 text-slate-500">
                <tr>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Folio</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Colaborador</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Tipo</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Días solicitados</th>
                    <th class="p-4 text-left text-xs font-bold uppercase tracking-wider">Estatus</th>
                    <th class="p-4 text-right text-xs font-bold uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($solicitudes as $s)
                    @php
                        $fechasIndividuales = $s->diasSeleccionados ?? collect();
                        $esHistorica = $s->esHistorica();
                        $estatusVisual = match (true) {
                            $esHistorica => ['Registro histórico', 'bg-slate-100 text-slate-700', 'bg-slate-500'],
                            $s->estaAprobada() => ['Aprobada', 'bg-emerald-100 text-emerald-700', 'bg-emerald-500'],
                            $s->estatus === 'rechazado' => ['Rechazada', 'bg-rose-100 text-rose-700', 'bg-rose-500'],
                            $s->estatus === 'cancelado' => ['Cancelada', 'bg-slate-200 text-slate-600', 'bg-slate-500'],
                            $s->estatus === 'con_observaciones' => ['Con observaciones', 'bg-amber-100 text-amber-800', 'bg-amber-500'],
                            default => ['Pendiente de formato', 'bg-violet-100 text-violet-700', 'bg-violet-500'],
                        };
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
                            <span class="inline-flex rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">{{ $s->tipoPermiso?->nombre }}</span>
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
                                <div class="text-xs text-slate-500">{{ $s->fecha_inicio?->format('d/m/Y') }}{{ $s->fecha_fin && $s->fecha_inicio && $s->fecha_fin->ne($s->fecha_inicio) ? ' al '.$s->fecha_fin->format('d/m/Y') : '' }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-black {{ $estatusVisual[1] }}">
                                <span class="h-2 w-2 rounded-full {{ $estatusVisual[2] }}"></span>{{ $estatusVisual[0] }}
                            </span>
                            @if(!$s->archivo_firmado_path && !$esHistorica && !$s->estaAprobada())
                                <div class="mt-1 text-[11px] text-slate-400">Sin formato firmado</div>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.permisos.show', $s) }}" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-700 shadow-sm hover:border-violet-300 hover:text-violet-700">Ver solicitud</a>
                                <form method="POST" action="{{ route('admin.permisos.destroy', $s) }}" onsubmit="return confirm('¿Eliminar definitivamente la solicitud #{{ $s->id }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex rounded-xl border border-rose-200 bg-white px-3 py-2 font-bold text-rose-600 hover:bg-rose-50">Eliminar</button>
                                </form>
                            </div>
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
