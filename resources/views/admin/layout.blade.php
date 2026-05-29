<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Panel RH')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="min-h-screen flex">
        <aside class="w-72 bg-slate-950 text-white hidden md:flex md:flex-col">
            <div class="p-6 border-b border-slate-800">
                <div class="text-xl font-bold">Panel RH</div>
                <div class="text-sm text-slate-400 mt-1">Formularios internos</div>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
                    Inicio
                </a>

                <a href="{{ route('admin.formularios.index') }}"
                   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.formularios.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
                    Formularios
                </a>

                <a href="{{ route('admin.usuarios.index') }}"
                   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.usuarios.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
                    Usuarios
                </a>

                <a href="{{ route('admin.respuestas.index') }}"
                   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.respuestas.*') || request()->routeIs('admin.respuesta.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
                    Respuestas
                </a>

                <a href="{{ route('admin.fields.index') }}"
                   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.fields.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
                    Campos del formulario
                </a>

                <a href="{{ route('admin.catalogos.index') }}"
                   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.catalogos.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
                    Catálogos
                </a>

                <a href="{{ route('admin.import.view') }}"
                   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.import.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
                    Importar Excel
                </a>

                <a href="{{ route('form.index') }}"
                   target="_blank"
                   class="block px-4 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800">
                    Ver formulario público
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                            class="w-full px-4 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1">
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">@yield('page_title', 'Panel RH')</h1>
                    <p class="text-sm text-slate-500">@yield('page_description', 'Administración del formulario')</p>
                </div>

                <div class="text-right">
                    <div class="text-sm font-semibold">{{ auth()->user()->name ?? 'Usuario' }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </header>

            <div class="md:hidden bg-slate-950 text-white p-3 flex gap-2 overflow-x-auto">
                <a href="{{ route('admin.dashboard') }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
                    Inicio
                </a>

                <a href="{{ route('admin.formularios.index') }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.formularios.*') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
                    Formularios
                </a>

                <a href="{{ route('admin.usuarios.index') }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.usuarios.*') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
                    Usuarios
                </a>

                <a href="{{ route('admin.respuestas.index') }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.respuestas.*') || request()->routeIs('admin.respuesta.*') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
                    Respuestas
                </a>

                <a href="{{ route('admin.fields.index') }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.fields.*') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
                    Campos
                </a>

                <a href="{{ route('admin.catalogos.index') }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.catalogos.*') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
                    Catálogos
                </a>

                <a href="{{ route('admin.import.view') }}"
                   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.import.*') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
                    Excel
                </a>
            </div>

            <section class="p-6">
                @if(session('success'))
                    <div class="mb-5 rounded-xl bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                        <div class="font-semibold mb-2">Revisa estos errores:</div>

                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
