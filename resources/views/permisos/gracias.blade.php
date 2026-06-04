<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud enviada</title>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-2xl mx-auto py-16 px-4">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 text-center">
            <div class="text-4xl mb-4">✅</div>
            <h1 class="text-2xl font-bold">Solicitud registrada</h1>
            <p class="text-slate-500 mt-3">
                El formato fue generado y enviado al colaborador, al líder y a RH para seguimiento físico.
            </p>
            <a href="{{ route('permisos.solicitud.create') }}" class="inline-flex mt-6 rounded-xl bg-slate-950 text-white px-5 py-3 hover:bg-slate-800">
                Crear otra solicitud
            </a>
        </div>
    </div>
</body>
</html>
