<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Firmar solicitud</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.design-assets')
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-950 text-white p-6">
                <h1 class="text-2xl font-bold">Firma de solicitud</h1>
                <p class="text-slate-300">{{ $firma->solicitud->tipoPermiso?->nombre }} · {{ ucfirst($firma->tipo_firma) }}</p>
            </div>

            <div class="p-6 space-y-5">
                @if(session('success'))<div class="rounded-xl bg-green-100 border border-green-300 text-green-800 px-4 py-3">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 border border-slate-200 rounded-2xl p-5">
                    <div><div class="text-xs text-slate-500">Colaborador</div><div class="font-bold">{{ $firma->solicitud->empleado?->nombre }}</div></div>
                    <div><div class="text-xs text-slate-500">Área</div><div class="font-bold">{{ $firma->solicitud->empleado?->area?->nombre }}</div></div>
                    <div><div class="text-xs text-slate-500">Días</div><div class="font-bold">{{ $firma->solicitud->dias_solicitados }}</div></div>
                    <div><div class="text-xs text-slate-500">Inicio</div><div class="font-bold">{{ $firma->solicitud->fecha_inicio?->format('d/m/Y') }}</div></div>
                    <div><div class="text-xs text-slate-500">Fin</div><div class="font-bold">{{ $firma->solicitud->fecha_fin?->format('d/m/Y') }}</div></div>
                    <div><div class="text-xs text-slate-500">Estatus</div><div class="font-bold">{{ $firma->solicitud->estatus_label }}</div></div>
                </div>

                @if($firma->estatus === 'firmado')
                    <div class="rounded-2xl bg-green-50 border border-green-200 text-green-800 p-5">
                        Este documento ya fue firmado el {{ $firma->firmado_at?->format('d/m/Y H:i') }}.
                    </div>
                @else
                    <form method="POST" action="{{ route('permisos.firma.store', $firma->token) }}" onsubmit="return prepararFirma()" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold mb-2">Firma aquí</label>
                            <canvas id="signature-pad" class="w-full h-56 bg-white border-2 border-dashed border-slate-300 rounded-2xl"></canvas>
                            <input type="hidden" name="firma_data" id="firma_data">
                            <button type="button" id="clear" class="mt-2 text-sm text-red-600 underline">Limpiar firma</button>
                        </div>

                        <label class="flex gap-2 items-start">
                            <input type="checkbox" name="acepto" value="1" class="mt-1 rounded" required>
                            <span>Acepto que esta firma representa mi conformidad y queda registrada con fecha, hora, IP y navegador.</span>
                        </label>

                        <div class="flex justify-end">
                            <button class="rounded-xl bg-slate-950 text-white px-6 py-3">Firmar documento</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('signature-pad');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let drawing = false;
            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);
            function pos(e) {
                const rect = canvas.getBoundingClientRect();
                const t = e.touches ? e.touches[0] : e;
                return { x: t.clientX - rect.left, y: t.clientY - rect.top };
            }
            function start(e) { drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); }
            function move(e) { if (!drawing) return; const p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); e.preventDefault(); }
            function end() { drawing = false; }
            canvas.addEventListener('mousedown', start); canvas.addEventListener('mousemove', move); canvas.addEventListener('mouseup', end); canvas.addEventListener('mouseleave', end);
            canvas.addEventListener('touchstart', start); canvas.addEventListener('touchmove', move); canvas.addEventListener('touchend', end);
            document.getElementById('clear').addEventListener('click', () => ctx.clearRect(0, 0, canvas.width, canvas.height));
        }
        function prepararFirma() {
            const input = document.getElementById('firma_data');
            input.value = canvas.toDataURL('image/png');
            return true;
        }
    </script>
</body>
</html>
