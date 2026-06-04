<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato de permiso / ausencia</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2>Formato de permiso / ausencia #{{ $solicitud->id }}</h2>

    @if($destinatarioTipo === 'lider')
        <p>Hola {{ $solicitud->lider?->nombre ?? 'líder' }},</p>
        <p>
            Se generó una solicitud de permiso/ausencia para
            <strong>{{ $solicitud->empleado?->nombre }}</strong>.
            El formato viene adjunto para que puedas descargarlo, revisarlo y firmarlo.
        </p>
        <p>
            Después de firmarlo, favor de entregarlo al colaborador para que también lo firme
            y posteriormente lo entregue físicamente a RH.
        </p>
    @elseif($destinatarioTipo === 'colaborador')
        <p>Hola {{ $solicitud->empleado?->nombre }},</p>
        <p>
            Tu formato de permiso/ausencia fue generado y enviado también a tu líder directo.
            Una vez firmado por tu líder, deberás firmarlo y entregarlo físicamente a RH.
        </p>
    @else
        <p>Hola RH,</p>
        <p>
            Se generó una nueva solicitud de permiso/ausencia. El formato inicial viene adjunto
            para seguimiento.
        </p>
    @endif

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; margin-top: 15px;">
        <tr><td><strong>Tipo</strong></td><td>{{ $solicitud->tipoPermiso?->nombre }}</td></tr>
        <tr><td><strong>Colaborador</strong></td><td>{{ $solicitud->empleado?->nombre }}</td></tr>
        <tr><td><strong>Área</strong></td><td>{{ $solicitud->area?->nombre ?? $solicitud->empleado?->area?->nombre }}</td></tr>
        <tr><td><strong>Líder</strong></td><td>{{ $solicitud->lider?->nombre ?? $solicitud->empleado?->lider?->nombre }}</td></tr>
        <tr><td><strong>Periodo</strong></td><td>{{ $solicitud->fecha_inicio?->format('d/m/Y') }} al {{ $solicitud->fecha_fin?->format('d/m/Y') }}</td></tr>
        <tr><td><strong>Días</strong></td><td>{{ $solicitud->dias_solicitados }}</td></tr>
        <tr><td><strong>Motivo</strong></td><td>{{ $solicitud->motivo }}</td></tr>
    </table>

    <p style="margin-top: 20px;">
        Este correo fue generado automáticamente por el Sistema de Formularios RH.
    </p>
</body>
</html>
