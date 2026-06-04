<h2>Firma requerida</h2>
<p>Hola {{ $firma->nombre }},</p>

<p>Se generó una solicitud de <strong>{{ $solicitud->tipoPermiso?->nombre }}</strong> para el colaborador <strong>{{ $solicitud->empleado?->nombre }}</strong>.</p>

<ul>
    <li><strong>Fecha inicio:</strong> {{ $solicitud->fecha_inicio?->format('d/m/Y') }}</li>
    <li><strong>Fecha fin:</strong> {{ $solicitud->fecha_fin?->format('d/m/Y') }}</li>
    <li><strong>Días:</strong> {{ $solicitud->dias_solicitados }}</li>
</ul>

<p>Por favor revisa y firma el formato desde el siguiente enlace:</p>

<p><a href="{{ $urlFirma }}" style="display:inline-block;background:#0f172a;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;">Firmar documento</a></p>

<p>Si el botón no abre, copia y pega este enlace en tu navegador:</p>
<p>{{ $urlFirma }}</p>
