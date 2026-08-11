<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias</title>
    @include('partials.design-assets')
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 max-w-lg text-center">
            <h1 class="text-3xl font-bold mb-3">Formulario enviado</h1>
            <p class="text-slate-500">Gracias. La información fue recibida correctamente.</p>
            <a href="{{ route('form.index') }}" class="mt-6 inline-flex rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">Volver</a>
        </div>
    </div>
</body>
</html>
