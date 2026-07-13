@extends('admin.layout')

@section('title', 'Detalle de solicitud')
@section('page_title', 'Solicitud #' . $permiso->id)
@section('page_description', 'Consulta del permiso o vacaciones registradas.')

@section('content')
@php
    $esHistorica = str_contains(mb_strtolower((string) $permiso->motivo), 'históric')
        || str_contains(mb_strtolower((string) $permiso->motivo), 'histor');
    $fechasIndividuales = $permiso->dias ?? collect();
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.permisos.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-bold text-slate-700 shadow-sm hover:bg-slate-50">
        ← Volver a solicitudes
    </a>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.permisos.descargar', $permiso) }}" class="rounded-xl bg-slate-950 px-4 py-2.5 font-bold text-white hover:bg-slate-800">Descargar formato</a>
        @if($permiso->archivo_firmado_path)
            <a href="{{ route('admin.permisos.formato_firmado.descargar', $permiso) }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white hover:bg-emerald-700">Descargar firmado</a>
        @endif
    </div>
</div>

<div class="mb-6 overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-500 p-7 text-white shadow-xl shadow-emerald-600/15 relative">
    <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
    <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-[.2em] text-emerald-100">Solicitud autorizada</div>
            <h1 class="mt-2 text-3xl font-black">Aprobada</h1>
            <p class="mt-2 text-emerald-50">Esta solicitud ya fue registrada y no requiere acciones adicionales de RH.</p>
        </div>
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white/20 text-4xl shadow-inner">✓</div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <section class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-xl text-violet-700">👤</div>
            <div>
                <h2 class="text-xl font-black text-slate-950">Información de la solicitud</h2>
                <p class="text-sm text-slate-500">Datos del colaborador y fechas autorizadas.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 text-sm md:grid-cols-2">
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Colaborador</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->empleado?->nombre }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Correo</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->empleado?->correo ?: 'Sin correo registrado' }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Departamento</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->area?->nombre ?? $permiso->empleado?->area?->nombre }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Líder</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->lider?->nombre ?? $permiso->empleado?->lider?->nombre ?? 'Sin líder asignado' }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tipo</span><div class="mt-1 font-bold text-violet-700">{{ $permiso->tipoPermiso?->nombre }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total autorizado</span><div class="mt-1 text-2xl font-black text-slate-950">{{ number_format((float) $permiso->dias_solicitados, 0) }} día(s)</div></div>
        </div>

        <div class="mt-6 rounded-3xl border border-violet-100 bg-violet-50/50 p-5">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-black text-slate-900">Días autorizados</h3>
                    <p class="text-xs text-slate-500">En vacaciones salteadas solo se consideran estas fechas.</p>
                </div>
                <span class="rounded-full bg-violet-600 px-3 py-1 text-xs font-bold text-white">{{ number_format((float) $permiso->dias_solicitados, 0) }} día(s)</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($fechasIndividuales->count())
                    @foreach($fechasIndividuales as $dia)
                        <span class="rounded-xl border border-violet-200 bg-white px-3 py-2 text-sm font-bold text-violet-800 shadow-sm">{{ $dia->fecha?->format('d/m/Y') }}</span>
                    @endforeach
                @else
                    <span class="rounded-xl border border-violet-200 bg-white px-3 py-2 text-sm font-bold text-violet-800 shadow-sm">{{ $permiso->fecha_inicio?->format('d/m/Y') }}</span>
                    @if($permiso->fecha_fin && $permiso->fecha_fin->ne($permiso->fecha_inicio))
                        <span class="rounded-xl border border-violet-200 bg-white px-3 py-2 text-sm font-bold text-violet-800 shadow-sm">{{ $permiso->fecha_fin?->format('d/m/Y') }}</span>
                    @endif
                @endif
            </div>
        </div>

        @if(!$esHistorica && filled($permiso->motivo))
            <div class="mt-6 rounded-2xl border border-slate-200 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Comentario del colaborador</div>
                <div class="mt-2 text-sm text-slate-700">{{ $permiso->motivo }}</div>
            </div>
        @endif
    </section>

    <aside class="space-y-6">
        <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-700">✓</div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Estatus</div>
                    <div class="text-xl font-black text-emerald-800">Aprobada</div>
                </div>
            </div>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-3 border-t border-emerald-100 pt-3"><span class="text-slate-500">Fecha de registro</span><strong>{{ $permiso->created_at?->format('d/m/Y') }}</strong></div>
                <div class="flex justify-between gap-3 border-t border-emerald-100 pt-3"><span class="text-slate-500">Folio</span><strong>#{{ $permiso->id }}</strong></div>
                <div class="flex justify-between gap-3 border-t border-emerald-100 pt-3"><span class="text-slate-500">Documento</span><strong>{{ $permiso->documento_enviado_at ? 'Generado' : 'Registro histórico' }}</strong></div>
            </div>
        </div>

        @if(!$esHistorica)
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-black text-slate-900">Formato firmado</h3>
                @if($permiso->archivo_firmado_path)
                    <p class="mt-2 text-sm text-emerald-700">El documento firmado ya está disponible.</p>
                    <a href="{{ route('admin.permisos.formato_firmado.descargar', $permiso) }}" class="mt-4 inline-flex w-full justify-center rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white">Descargar firmado</a>
                @else
                    <p class="mt-2 text-sm text-slate-500">Todavía no se ha cargado el archivo firmado.</p>
                @endif
            </div>
        @endif
    </aside>
</div>
@endsection
