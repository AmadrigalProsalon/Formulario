@extends('admin.layout')

@section('title', 'Detalle de solicitud')
@section('page_title', 'Solicitud #' . $permiso->id)
@section('page_description', 'Revisión y seguimiento de vacaciones o permisos.')

@section('content')
@php
    $esHistorica = $permiso->esHistorica();
    $estaAprobada = $permiso->estaAprobada();
    $estaRechazada = $permiso->estatus === 'rechazado';
    $estaCancelada = $permiso->estatus === 'cancelado';
    $estaPendiente = ! $esHistorica && ! $estaAprobada && ! $estaRechazada && ! $estaCancelada;
    $fechasIndividuales = $permiso->diasSeleccionados ?? collect();

    $estado = match (true) {
        $esHistorica => [
            'etiqueta' => 'Registro histórico',
            'titulo' => 'Movimiento histórico',
            'descripcion' => 'Este registro proviene de la importación del historial y no requiere aprobación de RH.',
            'icono' => '↺',
            'banner' => 'from-slate-700 via-slate-600 to-slate-500',
            'suave' => 'border-slate-200 bg-slate-50',
            'texto' => 'text-slate-800',
        ],
        $estaAprobada => [
            'etiqueta' => 'Aprobada',
            'titulo' => 'Solicitud aprobada',
            'descripcion' => 'RH autorizó la solicitud. El formato firmado es opcional y puede adjuntarse como soporte.',
            'icono' => '✓',
            'banner' => 'from-emerald-600 via-emerald-500 to-teal-500',
            'suave' => 'border-emerald-200 bg-emerald-50',
            'texto' => 'text-emerald-800',
        ],
        $estaRechazada => [
            'etiqueta' => 'Rechazada',
            'titulo' => 'Solicitud rechazada',
            'descripcion' => 'RH rechazó la solicitud. Los días no se descuentan ni permanecen reservados.',
            'icono' => '×',
            'banner' => 'from-rose-700 via-rose-600 to-red-500',
            'suave' => 'border-rose-200 bg-rose-50',
            'texto' => 'text-rose-800',
        ],
        $estaCancelada => [
            'etiqueta' => 'Cancelada',
            'titulo' => 'Solicitud cancelada',
            'descripcion' => 'La solicitud fue cancelada y ya no afecta el saldo del colaborador.',
            'icono' => '—',
            'banner' => 'from-slate-700 via-slate-600 to-slate-500',
            'suave' => 'border-slate-200 bg-slate-50',
            'texto' => 'text-slate-800',
        ],
        $permiso->estatus === 'con_observaciones' => [
            'etiqueta' => 'Con observaciones',
            'titulo' => 'Corrección requerida',
            'descripcion' => 'La solicitud sigue pendiente. RH puede corregir las observaciones y aprobarla cuando corresponda.',
            'icono' => '!',
            'banner' => 'from-amber-600 via-orange-500 to-amber-400',
            'suave' => 'border-amber-200 bg-amber-50',
            'texto' => 'text-amber-800',
        ],
        default => [
            'etiqueta' => 'Pendiente de formato',
            'titulo' => 'Pendiente de aprobación',
            'descripcion' => 'La solicitud está pendiente. RH puede aprobarla directamente o rechazarla; el formato firmado es opcional.',
            'icono' => '…',
            'banner' => 'from-indigo-700 via-violet-600 to-purple-500',
            'suave' => 'border-violet-200 bg-violet-50',
            'texto' => 'text-violet-800',
        ],
    };
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

<div class="relative mb-6 overflow-hidden rounded-[2rem] bg-gradient-to-br {{ $estado['banner'] }} p-7 text-white shadow-xl">
    <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
    <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-[.2em] text-white/75">{{ $estado['etiqueta'] }}</div>
            <h1 class="mt-2 text-3xl font-black">{{ $estado['titulo'] }}</h1>
            <p class="mt-2 max-w-3xl text-white/90">{{ $estado['descripcion'] }}</p>
        </div>
        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-white/20 text-4xl shadow-inner">{{ $estado['icono'] }}</div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8 xl:col-span-2">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-xl text-violet-700">👤</div>
            <div>
                <h2 class="text-xl font-black text-slate-950">Información de la solicitud</h2>
                <p class="text-sm text-slate-500">Datos del colaborador y fechas solicitadas.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 text-sm md:grid-cols-2">
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Colaborador</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->empleado?->nombre }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Correo</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->empleado?->correo ?: 'Sin correo registrado' }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Departamento</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->area?->nombre ?? $permiso->empleado?->area?->nombre ?? 'Sin departamento' }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Líder</span><div class="mt-1 font-bold text-slate-900">{{ $permiso->lider?->nombre ?? $permiso->empleado?->lider?->nombre ?? 'Sin líder asignado' }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tipo</span><div class="mt-1 font-bold text-violet-700">{{ $permiso->tipoPermiso?->nombre }}</div></div>
            <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">Días solicitados</span><div class="mt-1 text-2xl font-black text-slate-950">{{ number_format((float) $permiso->dias_solicitados, 0) }} día(s)</div></div>
        </div>

        <div class="mt-6 rounded-3xl border border-violet-100 bg-violet-50/50 p-5">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-black text-slate-900">Fechas solicitadas</h3>
                    <p class="text-xs text-slate-500">En vacaciones salteadas solo se consideran las fechas seleccionadas.</p>
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
                    @if($permiso->fecha_fin && $permiso->fecha_inicio && $permiso->fecha_fin->ne($permiso->fecha_inicio))
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

        @if(filled($permiso->observaciones_rh))
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-amber-700">Observaciones de RH</div>
                <div class="mt-2 text-sm text-amber-950">{{ $permiso->observaciones_rh }}</div>
            </div>
        @endif
    </section>

    <aside class="space-y-6">
        <div class="rounded-3xl border p-6 shadow-sm {{ $estado['suave'] }}">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm {{ $estado['texto'] }}">{{ $estado['icono'] }}</div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Estatus</div>
                    <div class="text-xl font-black {{ $estado['texto'] }}">{{ $estado['etiqueta'] }}</div>
                </div>
            </div>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-3 border-t border-black/5 pt-3"><span class="text-slate-500">Fecha de registro</span><strong>{{ $permiso->created_at?->format('d/m/Y') }}</strong></div>
                <div class="flex justify-between gap-3 border-t border-black/5 pt-3"><span class="text-slate-500">Folio</span><strong>#{{ $permiso->id }}</strong></div>
                <div class="flex justify-between gap-3 border-t border-black/5 pt-3"><span class="text-slate-500">Formato firmado (opcional)</span><strong>{{ $permiso->archivo_firmado_path ? 'Cargado' : 'Sin archivo' }}</strong></div>
            </div>
        </div>

        @if($estaPendiente)
            <div class="rounded-3xl border border-emerald-200 bg-white p-6 shadow-sm">
                <h3 class="font-black text-slate-900">Aprobar solicitud</h3>
                <p class="mt-2 text-sm text-slate-500">El formato firmado no es obligatorio. RH puede autorizar la solicitud directamente.</p>
                <form method="POST" action="{{ route('admin.permisos.recibido', $permiso) }}" class="mt-4 space-y-3" onsubmit="return confirm('¿Confirmas que deseas aprobar esta solicitud?');">
                    @csrf
                    <textarea name="observaciones_rh" rows="2" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Observaciones opcionales"></textarea>
                    <button class="w-full rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-700">Aprobar solicitud</button>
                </form>
            </div>

            <div class="rounded-3xl border border-rose-200 bg-white p-6 shadow-sm">
                <h3 class="font-black text-rose-900">Rechazar solicitud</h3>
                <p class="mt-2 text-sm text-slate-500">El motivo quedará registrado y los días se liberarán.</p>
                <form method="POST" action="{{ route('admin.permisos.rechazar', $permiso) }}" class="mt-4 space-y-3" onsubmit="return confirm('¿Confirmas que deseas rechazar esta solicitud?');">
                    @csrf
                    <textarea name="observaciones_rh" rows="3" required class="w-full rounded-xl border-rose-200 text-sm focus:border-rose-500 focus:ring-rose-500" placeholder="Motivo del rechazo"></textarea>
                    <button class="w-full rounded-xl bg-rose-600 px-4 py-3 font-bold text-white hover:bg-rose-700">Rechazar solicitud</button>
                </form>
            </div>
        @endif

        @if(!$esHistorica && !$estaRechazada && !$estaCancelada)
            <div class="rounded-3xl border border-indigo-200 bg-white p-6 shadow-sm">
                <h3 class="font-black text-slate-900">Formato firmado <span class="text-sm font-semibold text-slate-400">(opcional)</span></h3>
                <p class="mt-2 text-sm text-slate-500">Puedes adjuntarlo como soporte sin cambiar el estatus de la solicitud.</p>

                @if($permiso->archivo_firmado_path)
                    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        Archivo cargado el {{ $permiso->archivo_firmado_at?->format('d/m/Y H:i') ?? '—' }}.
                    </div>
                    <a href="{{ route('admin.permisos.formato_firmado.descargar', $permiso) }}" class="mt-3 inline-flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-2.5 font-bold text-white hover:bg-indigo-700">Descargar archivo</a>
                @endif

                <form method="POST" action="{{ route('admin.permisos.formato_firmado.subir', $permiso) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <input type="file" name="archivo_firmado" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="block w-full rounded-xl border border-slate-300 bg-slate-50 p-2 text-sm">
                    <textarea name="observaciones_rh" rows="2" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Observaciones opcionales"></textarea>
                    <button class="w-full rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 font-bold text-indigo-700 hover:bg-indigo-100">{{ $permiso->archivo_firmado_path ? 'Reemplazar archivo' : 'Adjuntar formato' }}</button>
                </form>
            </div>
        @endif

        <div class="rounded-3xl border border-red-200 bg-red-50 p-6 shadow-sm">
            <h3 class="font-black text-red-900">Eliminar solicitud</h3>
            <p class="mt-2 text-sm text-red-700">La eliminación es definitiva e incluye historial y archivos relacionados.</p>
            <form method="POST" action="{{ route('admin.permisos.destroy', $permiso) }}" class="mt-4" onsubmit="return confirm('Esta acción no se puede deshacer. ¿Eliminar definitivamente la solicitud #{{ $permiso->id }}?');">
                @csrf
                @method('DELETE')
                <button class="w-full rounded-xl border border-red-300 bg-white px-4 py-3 font-bold text-red-700 hover:bg-red-100">Eliminar definitivamente</button>
            </form>
        </div>
    </aside>
</div>
@endsection
