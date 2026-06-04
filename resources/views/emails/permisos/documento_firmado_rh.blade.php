<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato firmado listo para RH</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827; line-height:1.5;">
    <h2>Formato firmado listo para RH</h2>

    <p>El colaborador y su líder ya firmaron el formato de permiso / ausencia.</p>

    <table cellpadding="6" cellspacing="0" border="1" style="border-collapse: collapse; border-color:#d1d5db;">
        <tr>
            <td><strong>Folio</strong></td>
            <td>#{{ $solicitud->id }}</td>
        </tr>
        <tr>
            <td><strong>Colaborador</strong></td>
            <td>{{ $solicitud->empleado->nombre ?? $solicitud->nombre_colaborador ?? 'Sin dato' }}</td>
        </tr>
        <tr>
            <td><strong>Líder</strong></td>
            <td>{{ $solicitud->lider->nombre ?? $solicitud->lider_nombre ?? 'Sin dato' }}</td>
        </tr>
        <tr>
            <td><strong>Fechas</strong></td>
            <td>{{ $solicitud->fecha_inicio }} al {{ $solicitud->fecha_fin }}</td>
        </tr>
        <tr>
            <td><strong>Días solicitados</strong></td>
            <td>{{ $solicitud->dias_solicitados }}</td>
        </tr>
    </table>

    <p style="margin-top:16px;">
        El documento firmado va adjunto. RH puede revisarlo y marcarlo como <strong>Formato recibido</strong>, <strong>Formato pendiente</strong> o <strong>Con observaciones</strong> en el panel.
    </p>
</body>
</html>
