<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud registrada</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="max-w-lg bg-white rounded-3xl shadow-sm border border-slate-200 p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-green-100 text-green-700 mx-auto flex items-center justify-center text-3xl mb-4">✓</div>
        <h1 class="text-2xl font-bold">Solicitud registrada</h1>
        <p class="text-slate-500 mt-2">Se enviaron los enlaces de firma al colaborador y al líder correspondiente. RH podrá consultar el avance desde el panel.</p>
        <a href="{{ route('permisos.solicitud') }}" class="inline-flex mt-6 rounded-xl bg-slate-950 text-white px-5 py-2.5">Crear otra solicitud</a>
    </div>
</body>
</html>
