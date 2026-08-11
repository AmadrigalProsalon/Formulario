<?php

return [
    // Correo principal de RH para recibir formatos/documentos.
    // Si no existe PERMISOS_RH_EMAIL en .env, usará este correo por defecto.
    'rh_email' => env('PERMISOS_RH_EMAIL', env('RH_FORM_MAIL_TO', 'rhformularios@prosalon.mx')),

    // La firma digital queda desactivada por ahora. El flujo actual es firma física.
    'firma_digital' => filter_var(env('PERMISOS_FIRMA_DIGITAL', false), FILTER_VALIDATE_BOOL),

    'documentos_disk' => env('PERMISOS_DOCUMENTOS_DISK', 'public'),
    'template_path' => env('PERMISOS_TEMPLATE_PATH'),
    'template_default' => resource_path('templates/formato_permiso.docx'),

    // Vacaciones solo se descuentan cuando RH marca formato recibido.
    'descontar_vacaciones_en_estatus' => ['formato_recibido'],

    'estatus_activos_para_cruce' => [
        'formato_generado',
        'formato_enviado',
        'formato_pendiente',
        'formato_recibido',
        'con_observaciones',
        'historico',
    ],

    'estatus_no_activos' => ['cancelado', 'rechazado'],
];
