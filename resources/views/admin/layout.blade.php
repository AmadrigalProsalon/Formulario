<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel RH')</title>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="min-h-screen flex">
        <aside class="hidden lg:flex lg:w-72 xl:w-80 bg-slate-950 text-white h-screen sticky top-0 flex-col overflow-hidden shrink-0">
            <div class="p-6 border-b border-slate-800 shrink-0">
                <div class="text-xl font-black tracking-tight">Panel RH</div>
                <div class="text-xs text-slate-400 mt-1">Formularios, permisos y vacantes</div>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-5">
                <div>
                    <div class="px-3 text-[11px] uppercase tracking-wider text-slate-500 font-bold mb-2">Principal</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Inicio</a>
                        @if(Route::has('form.index'))
                            <a href="{{ url('/permisos/solicitud')}}" target="_blank" class="block px-4 py-2.5 rounded-xl transition text-slate-300 hover:bg-slate-800">Solicitud de Vacaciones</a>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="px-3 text-[11px] uppercase tracking-wider text-slate-500 font-bold mb-2">Formularios</div>
                    <div class="space-y-1">
                        @if(Route::has('admin.formularios.index'))
                            <a href="{{ route('admin.formularios.index') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.formularios.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Formularios</a>
                        @endif
                        @if(Route::has('admin.fields.index'))
                            <a href="{{ route('admin.fields.index') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.fields.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Campos</a>
                        @endif
                        @if(Route::has('admin.catalogos.index'))
                            <a href="{{ route('admin.catalogos.index') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.catalogos.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Catálogos</a>
                        @endif
                        @if(Route::has('admin.respuestas.index'))
                            <a href="{{ route('admin.respuestas.index') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.respuestas.*') || request()->routeIs('admin.respuesta.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Respuestas</a>
                        @endif
                    </div>
                </div>

<div>
    <div class="px-3 text-[11px] uppercase tracking-wider text-slate-500 font-bold mb-2">
        Permisos y ausencias
    </div>

    <div class="space-y-1">
        @if(Route::has('admin.permisos.index'))
            <a href="{{ route('admin.permisos.index') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos.index') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Solicitudes
            </a>
        @endif

        @if(Route::has('admin.ausencias.calendario'))
            <a href="{{ route('admin.ausencias.calendario') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.ausencias.calendario') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Calendario de ausencias
            </a>
        @elseif(Route::has('admin.permisos.calendario'))
            <a href="{{ route('admin.permisos.calendario') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos.calendario') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Calendario de ausencias
            </a>
        @endif

        @if(Route::has('admin.permisos.empleados.index'))
            <a href="{{ route('admin.permisos.empleados.index') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos.empleados.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Empleados
            </a>
        @elseif(Route::has('admin.permisos-catalogos.empleados.index'))
            <a href="{{ route('admin.permisos-catalogos.empleados.index') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos-catalogos.empleados.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Empleados
            </a>
        @endif

        @if(Route::has('admin.permisos.areas.index'))
            <a href="{{ route('admin.permisos.areas.index') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos.areas.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Áreas
            </a>
        @elseif(Route::has('admin.permisos-catalogos.areas.index'))
            <a href="{{ route('admin.permisos-catalogos.areas.index') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos-catalogos.areas.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Áreas
            </a>
        @endif

        @if(Route::has('admin.permisos.tipos.index'))
            <a href="{{ route('admin.permisos.tipos.index') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos.tipos.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Tipos de permiso
            </a>
        @elseif(Route::has('admin.permisos-catalogos.tipos.index'))
            <a href="{{ route('admin.permisos-catalogos.tipos.index') }}"
               class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.permisos-catalogos.tipos.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Tipos de permiso
            </a>
        @endif
    </div>
</div>
                <div>
                    <div class="px-3 text-[11px] uppercase tracking-wider text-slate-500 font-bold mb-2">Vacantes</div>
                    <div class="space-y-1">
                        @if(Route::has('admin.perfiles-puesto-csv.index'))
                            <a href="{{ route('admin.perfiles-puesto-csv.index') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.perfiles-puesto-csv.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Perfiles por CSV</a>
                        @endif
                        @if(Route::has('admin.perfiles-puesto.index'))
                            <a href="{{ route('admin.perfiles-puesto.index') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.perfiles-puesto.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Perfiles de puesto</a>
                        @endif
                        @if(Route::has('form.show'))
                            <a href="{{ route('form.show', 'requisicion-personal') }}" target="_blank" class="block px-4 py-2.5 rounded-xl transition text-slate-300 hover:bg-slate-800">Requisición de personal</a>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="px-3 text-[11px] uppercase tracking-wider text-slate-500 font-bold mb-2">Sistema</div>
                    <div class="space-y-1">
                        @if(Route::has('admin.usuarios.index'))
                            <a href="{{ route('admin.usuarios.index') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.usuarios.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Usuarios</a>
                        @endif
                        @if(Route::has('admin.import.view'))
                            <a href="{{ route('admin.import.view') }}" class="block px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.import.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">Importar Excel</a>
                        @endif
                    </div>
                </div>
            <a href="{{ route('admin.perfiles-puesto.csv') }}"
   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.perfiles-puesto.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
    Perfiles de puesto
</a>
</nav>

            <div class="p-4 border-t border-slate-800 shrink-0 bg-slate-950">
                <div class="text-xs text-slate-500 mb-2 truncate">{{ auth()->user()->email ?? '' }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-slate-800 text-white px-4 py-2.5 hover:bg-slate-700 text-sm font-semibold whitespace-nowrap overflow-hidden text-ellipsis">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="lg:hidden bg-slate-950 text-white p-4 sticky top-0 z-50">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="font-black">Panel RH</div>
                        <div class="text-xs text-slate-400">Formularios y permisos</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button class="rounded-xl bg-slate-800 px-3 py-2 text-sm whitespace-nowrap">Salir</button>
                    </form>
                </div>
                <div class="mt-4 overflow-x-auto flex gap-2 pb-1">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">Inicio</a>
                    @if(Route::has('admin.formularios.index'))<a href="{{ route('admin.formularios.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap bg-slate-800">Formularios</a>@endif
                    @if(Route::has('admin.permisos.index'))<a href="{{ route('admin.permisos.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap bg-slate-800">Permisos</a>@endif
                    @if(Route::has('admin.permisos.calendario'))<a href="{{ route('admin.permisos.calendario') }}" class="px-3 py-2 rounded-lg whitespace-nowrap bg-slate-800">Calendario</a>@endif
                    @if(Route::has('admin.usuarios.index'))<a href="{{ route('admin.usuarios.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap bg-slate-800">Usuarios</a>@endif
                </div>
            </header>

            <main class="p-4 md:p-8 max-w-[1600px] mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl md:text-3xl font-black text-slate-950">@yield('page_title', 'Panel RH')</h1>
                    <p class="text-slate-500 mt-1">@yield('page_description', 'Administración del sistema RH.')</p>
                </div>

                @if(session('success'))
                    <div class="mb-5 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-800 px-4 py-3">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                        <div class="font-bold mb-2">Revisa la información:</div>
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
