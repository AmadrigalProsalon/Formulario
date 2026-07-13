@extends('admin.layout')

@section('title', 'Empleados')
@section('page_title', 'Empleados')
@section('page_description', 'Administra datos, líderes, saldos y horarios laborales de los colaboradores.')

@section('content')
@php
    $activosPagina = $empleados->getCollection()->where('activo', true)->count();
    $lideresPagina = $empleados->getCollection()->where('es_lider', true)->count();
@endphp

<div class="mb-7 overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-indigo-950 to-violet-900 p-7 text-white shadow-xl shadow-indigo-950/10 md:p-9 relative">
    <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-violet-400/20 blur-3xl"></div>
    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-[.22em] text-indigo-200">Directorio RH</div>
            <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">Empleados y saldos</h1>
            <p class="mt-2 max-w-2xl text-sm text-indigo-100 md:text-base">Consulta y actualiza la información de cada colaborador sin perder de vista su líder, horario y saldo oficial.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if(Route::has('admin.permisos.empleados.importar'))
                <a href="{{ route('admin.permisos.empleados.importar') }}" class="rounded-2xl bg-white px-5 py-3 font-bold text-indigo-950 shadow-lg hover:bg-indigo-50">↑ Importar Excel/CSV</a>
            @endif
        </div>
    </div>
</div>

<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total empleados</div><div class="mt-2 text-3xl font-black text-slate-950">{{ $empleados->total() }}</div></div>
    <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Activos en página</div><div class="mt-2 text-3xl font-black text-emerald-700">{{ $activosPagina }}</div></div>
    <div class="rounded-3xl border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-violet-600">Líderes en página</div><div class="mt-2 text-3xl font-black text-violet-700">{{ $lideresPagina }}</div></div>
    <div class="rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-wider text-blue-600">Página actual</div><div class="mt-2 text-3xl font-black text-blue-700">{{ $empleados->currentPage() }}</div></div>
</div>

<div class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
    <form method="GET" action="{{ route('admin.permisos.empleados.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-[1.5fr_1fr_1fr_auto]">
        <div><label class="mb-1 block text-sm font-bold text-slate-700">Buscar colaborador</label><div class="relative"><span class="absolute left-4 top-3 text-slate-400">⌕</span><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-xl border-slate-300 pl-10 focus:border-violet-500 focus:ring-violet-500" placeholder="Nombre, CURP, RFC, correo, puesto o número"></div></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700">Departamento</label><select name="area_id" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500"><option value="">Todos</option>@foreach($areas as $area)<option value="{{ $area->id }}" @selected(($filters['area_id'] ?? '') == $area->id)>{{ $area->nombre }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700">Estado</label><select name="activo" class="w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500"><option value="">Todos</option><option value="1" @selected(($filters['activo'] ?? '') === '1')>Activos</option><option value="0" @selected(($filters['activo'] ?? '') === '0')>Inactivos</option></select></div>
        <div class="flex items-end gap-2"><button class="rounded-xl bg-slate-950 px-5 py-2.5 font-bold text-white hover:bg-slate-800">Filtrar</button><a href="{{ route('admin.permisos.empleados.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 font-semibold text-slate-600 hover:bg-slate-200">Limpiar</a></div>
    </form>
</div>

<div class="space-y-4">
    @forelse($empleados as $empleado)
        @php($diasEmpleado = $empleado->dias_laborales ?? [])
        <form id="empleado-{{ $empleado->id }}" method="POST" action="{{ route('admin.permisos.empleados.update', $empleado) }}" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
            @csrf @method('PUT')
            <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-violet-50/40 p-5 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-lg font-black text-white shadow-md">{{ strtoupper(substr($empleado->nombre ?? 'E', 0, 1)) }}</div>
                    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h2 class="truncate text-lg font-black text-slate-950">{{ $empleado->nombre }}</h2>@if($empleado->es_lider)<span class="rounded-full bg-violet-100 px-2.5 py-1 text-[11px] font-bold text-violet-700">Líder</span>@endif<span class="rounded-full {{ $empleado->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }} px-2.5 py-1 text-[11px] font-bold">{{ $empleado->activo ? 'Activo' : 'Inactivo' }}</span></div><p class="mt-1 truncate text-sm text-slate-500">#{{ $empleado->numero_empleado ?: 'Sin número' }} · {{ $empleado->puesto ?: 'Sin puesto' }}</p></div>
                </div>
                <button class="rounded-xl bg-slate-950 px-5 py-2.5 font-bold text-white shadow-sm hover:bg-slate-800">Guardar cambios</button>
            </div>

            <div class="grid grid-cols-1 gap-6 p-5 md:p-6 xl:grid-cols-12">
                <section class="xl:col-span-3"><div class="mb-3 text-xs font-black uppercase tracking-[.15em] text-slate-400">Identidad y contacto</div><div class="space-y-3"><input type="text" name="numero_empleado" value="{{ $empleado->numero_empleado }}" placeholder="Número de empleado" class="w-full rounded-xl border-slate-300"><input type="text" name="nombre" value="{{ $empleado->nombre }}" placeholder="Nombre completo" class="w-full rounded-xl border-slate-300 font-bold" required><input type="email" name="correo" value="{{ $empleado->correo }}" placeholder="Correo del colaborador" class="w-full rounded-xl border-slate-300"><div class="grid grid-cols-2 gap-3"><input type="text" name="curp" value="{{ $empleado->curp }}" placeholder="CURP" maxlength="18" class="w-full rounded-xl border-slate-300 uppercase"><input type="text" name="rfc" value="{{ $empleado->rfc }}" placeholder="RFC" maxlength="13" class="w-full rounded-xl border-slate-300 uppercase"></div></div></section>

                <section class="xl:col-span-3"><div class="mb-3 text-xs font-black uppercase tracking-[.15em] text-slate-400">Organización</div><div class="space-y-3"><select name="area_id" class="w-full rounded-xl border-slate-300"><option value="">Sin departamento</option>@foreach($areas as $area)<option value="{{ $area->id }}" @selected($empleado->area_id == $area->id)>{{ $area->nombre }}</option>@endforeach</select><select name="lider_id" class="w-full rounded-xl border-slate-300"><option value="">Sin líder</option>@foreach($lideres as $lider)<option value="{{ $lider->id }}" @selected($empleado->lider_id == $lider->id)>{{ $lider->nombre }}</option>@endforeach</select><input type="text" name="puesto" value="{{ $empleado->puesto }}" placeholder="Puesto" class="w-full rounded-xl border-slate-300"><label class="inline-flex items-center gap-2 rounded-xl bg-violet-50 px-3 py-2 text-sm font-semibold text-violet-800"><input type="checkbox" name="es_lider" value="1" class="rounded border-violet-300 text-violet-600" @checked($empleado->es_lider)> Es líder</label></div></section>

                <section class="xl:col-span-3"><div class="mb-3 text-xs font-black uppercase tracking-[.15em] text-slate-400">Ingreso y saldo</div><div class="space-y-3"><div><label class="mb-1 block text-xs font-bold text-slate-500">Fecha de ingreso</label><input type="date" name="fecha_ingreso" value="{{ $empleado->fecha_ingreso?->format('Y-m-d') }}" class="w-full rounded-xl border-slate-300"></div><div><label class="mb-1 block text-xs font-bold text-slate-500">Saldo oficial Excel</label><div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4"><div class="text-2xl font-black text-emerald-700">{{ number_format((float) ($empleado->vacaciones_pendientes ?? 0), 2) }}</div><div class="text-xs text-emerald-600">días disponibles</div></div></div><div><label class="mb-1 block text-xs font-bold text-slate-500">Ajuste manual</label><input type="number" step="0.01" name="vacaciones_ajuste" value="{{ number_format((float)($empleado->vacaciones_ajuste ?? 0), 2, '.', '') }}" class="w-full rounded-xl border-slate-300"></div></div></section>

                <section class="xl:col-span-3"><div class="mb-3 text-xs font-black uppercase tracking-[.15em] text-slate-400">Horario laboral especial</div><div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><div class="grid grid-cols-4 gap-2">@foreach([1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb',7=>'Dom'] as $numeroDia=>$etiquetaDia)<label class="cursor-pointer"><input type="checkbox" name="dias_laborales[]" value="{{ $numeroDia }}" class="peer sr-only" @checked(in_array($numeroDia,$diasEmpleado,true))><span class="flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-500 transition peer-checked:border-violet-500 peer-checked:bg-violet-600 peer-checked:text-white">{{ $etiquetaDia }}</span></label>@endforeach</div><p class="mt-3 text-xs leading-relaxed text-slate-500">Sin selección se usa el horario del área o L–V por defecto.</p></div><label class="mt-3 inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800"><input type="checkbox" name="activo" value="1" class="rounded border-emerald-300 text-emerald-600" @checked($empleado->activo)> Empleado activo</label></section>
            </div>
        </form>
    @empty
        <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white p-12 text-center text-slate-500">No hay empleados con esos filtros.</div>
    @endforelse
</div>

<div class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">{{ $empleados->links('vendor.pagination.rh') }}</div>
@endsection
