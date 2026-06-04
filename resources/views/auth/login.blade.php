<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Panel RH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4 text-slate-800">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
        <div class="mb-6 text-center">
            <div class="text-2xl font-bold">Panel RH</div>
            <p class="text-sm text-slate-500 mt-1">Acceso administrativo</p>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-1">Correo</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border-slate-300">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Contraseña</label>
                <input type="password" name="password" required class="w-full rounded-xl border-slate-300">
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                Recordarme
            </label>

            <button class="w-full rounded-xl bg-slate-950 text-white px-5 py-3 hover:bg-slate-800">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
