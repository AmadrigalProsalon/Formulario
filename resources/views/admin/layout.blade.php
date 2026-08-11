<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel RH')</title>
    @include('partials.design-assets')
</head>
<body class="bg-slate-100 text-slate-800">
@php
    $navItem = 'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition';
    $navIdle = 'text-slate-300 hover:bg-slate-800 hover:text-white';
    $navActive = 'bg-white text-slate-950 shadow-sm';
    $icon = 'w-5 h-5 shrink-0';
@endphp

<div class="min-h-screen lg:flex">
    <aside id="sidebarRh" class="fixed inset-y-0 left-0 z-50 w-80 -translate-x-full lg:translate-x-0 lg:static bg-slate-950 text-white flex flex-col transition-transform duration-200 shadow-2xl lg:shadow-none">
        <div class="px-5 py-5 border-b border-slate-800 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center font-black text-slate-950 shadow-lg shadow-blue-500/20">RH</div>
                <div class="min-w-0">
                    <div class="text-lg font-black tracking-tight truncate">Panel Recursos Humanos</div>
                    <div class="text-xs text-slate-400 truncate">Formularios y administración</div>
                </div>
            </a>
            <button type="button" id="cerrarMenuRh" class="lg:hidden rounded-lg p-2 text-slate-400 hover:bg-slate-800 hover:text-white" aria-label="Cerrar menú">✕</button>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
            <section>
                <div class="px-3 mb-2 text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Inicio</div>
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('admin.dashboard') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10M9 20v-6h6v6"/></svg>
                        Resumen
                    </a>
                    <a href="{{ url('/permisos/solicitud') }}" target="_blank" class="{{ $navItem }} bg-blue-500/10 text-blue-200 hover:bg-blue-500/20 hover:text-white">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva solicitud
                        <span class="ml-auto text-[10px] rounded-full bg-blue-400/20 px-2 py-0.5">Abrir</span>
                    </a>
                </div>
            </section>

            <section>
                <div class="px-3 mb-2 text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Vacaciones y permisos</div>
                <div class="space-y-1">
                    @if(Route::has('admin.permisos.index'))
                    <a href="{{ route('admin.permisos.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.permisos.index', 'admin.permisos.show') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v16H5V5a2 2 0 012-2z"/></svg>
                        Solicitudes
                    </a>
                    @endif
                    @if(Route::has('admin.ausencias.calendario'))
                    <a href="{{ route('admin.ausencias.calendario') }}" class="{{ $navItem }} {{ request()->routeIs('admin.ausencias.calendario') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 012 2v14H3V7a2 2 0 012-2z"/></svg>
                        Calendario de ausencias
                    </a>
                    @elseif(Route::has('admin.permisos.calendario'))
                    <a href="{{ route('admin.permisos.calendario') }}" class="{{ $navItem }} {{ request()->routeIs('admin.permisos.calendario') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 012 2v14H3V7a2 2 0 012-2z"/></svg>
                        Calendario de ausencias
                    </a>
                    @endif
                    @if(Route::has('admin.permisos.empleados.index'))
                    <a href="{{ route('admin.permisos.empleados.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.permisos.empleados.*') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm8 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                        Empleados y saldos
                    </a>
                    @endif
                    @if(Route::has('admin.permisos.empleados.importar'))
                    <a href="{{ route('admin.permisos.empleados.importar') }}" class="{{ $navItem }} {{ request()->routeIs('admin.permisos.empleados.importar*') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v4h16v-4"/></svg>
                        Importar saldos Excel/CSV
                    </a>
                    @endif
                    @if(Route::has('admin.permisos.calendario-laboral.index'))
                    <a href="{{ route('admin.permisos.calendario-laboral.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.permisos.calendario-laboral.*') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Horarios e inhábiles
                    </a>
                    @endif
                    @if(Route::has('admin.permisos.areas.index'))
                    <a href="{{ route('admin.permisos.areas.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.permisos.areas.*') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01"/></svg>
                        Áreas
                    </a>
                    @endif
                    @if(Route::has('admin.permisos.tipos.index'))
                    <a href="{{ route('admin.permisos.tipos.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.permisos.tipos.*') ? $navActive : $navIdle }}">
                        <svg class="{{ $icon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M3 11l8-8h7l3 3v7l-8 8L3 11z"/></svg>
                        Tipos de permiso
                    </a>
                    @endif
                </div>
            </section>

            <section>
                <div class="px-3 mb-2 text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Formularios</div>
                <div class="space-y-1">
                    @if(Route::has('admin.formularios.index'))
                    <a href="{{ route('admin.formularios.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.formularios.*') ? $navActive : $navIdle }}">Formularios</a>
                    @endif
                    @if(Route::has('admin.respuestas.index'))
                    <a href="{{ route('admin.respuestas.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.respuestas.*', 'admin.respuesta.*') ? $navActive : $navIdle }}">Respuestas</a>
                    @endif
                    @if(Route::has('admin.catalogos.index'))
                    <a href="{{ route('admin.catalogos.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.catalogos.*') ? $navActive : $navIdle }}">Catálogos generales</a>
                    @endif
                </div>
            </section>

            <section>
                <div class="px-3 mb-2 text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Vacantes</div>
                <div class="space-y-1">
                    @if(Route::has('admin.perfiles-puesto.index'))
                    <a href="{{ route('admin.perfiles-puesto.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.perfiles-puesto.*') ? $navActive : $navIdle }}">Perfiles de puesto</a>
                    @endif
                    @if(Route::has('admin.perfiles-puesto-csv.index'))
                    <a href="{{ route('admin.perfiles-puesto-csv.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.perfiles-puesto-csv.*') ? $navActive : $navIdle }}">Importar perfiles CSV</a>
                    @endif
                    @if(Route::has('form.show'))
                    <a href="{{ route('form.show', 'requisicion-personal') }}" target="_blank" class="{{ $navItem }} {{ $navIdle }}">Requisición de personal</a>
                    @endif
                </div>
            </section>

            <section>
                <div class="px-3 mb-2 text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Configuración</div>
                <div class="space-y-1">
                    @if(Route::has('admin.usuarios.index'))
                    <a href="{{ route('admin.usuarios.index') }}" class="{{ $navItem }} {{ request()->routeIs('admin.usuarios.*') ? $navActive : $navIdle }}">Usuarios</a>
                    @endif
                    @if(Route::has('admin.import.view'))
                    <a href="{{ route('admin.import.view') }}" class="{{ $navItem }} {{ request()->routeIs('admin.import.*') ? $navActive : $navIdle }}">Importación general</a>
                    @endif
                </div>
            </section>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <div class="rounded-2xl bg-slate-900 p-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold truncate">{{ auth()->user()->name ?? 'Usuario' }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs text-slate-400 hover:text-white" title="Cerrar sesión">Salir</button></form>
            </div>
        </div>
    </aside>

    <div id="overlayRh" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"></div>

    <main class="min-w-0 flex-1">
        <header class="lg:hidden sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 px-4 py-3 flex items-center justify-between">
            <button type="button" id="abrirMenuRh" class="rounded-xl border border-slate-200 p-2.5 text-slate-700" aria-label="Abrir menú">☰</button>
            <div class="font-black">Panel RH</div>
            <a href="{{ url('/permisos/solicitud') }}" class="rounded-xl bg-slate-950 text-white px-3 py-2 text-xs font-semibold">Nueva</a>
        </header>

        <div class="p-4 sm:p-6 lg:p-8 xl:p-10">
            @if(session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

<script>
(() => {
    const sidebar = document.getElementById('sidebarRh');
    const overlay = document.getElementById('overlayRh');
    const open = document.getElementById('abrirMenuRh');
    const close = document.getElementById('cerrarMenuRh');

    const show = () => {
        sidebar?.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };
    const hide = () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    open?.addEventListener('click', show);
    close?.addEventListener('click', hide);
    overlay?.addEventListener('click', hide);
})();
</script>
@stack('scripts')
</body>
</html>
