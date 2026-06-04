<?php

return [
    'rh_email' => env('PERMISOS_RH_EMAIL', env('RH_FORM_MAIL_TO', 'rh@prosalon.mx')),
    'firma_digital' => filter_var(env('PERMISOS_FIRMA_DIGITAL', false), FILTER_VALIDATE_BOOL),
    'documentos_disk' => env('PERMISOS_DOCUMENTOS_DISK', 'public'),
    'template_path' => env('PERMISOS_TEMPLATE_PATH'),
    'template_default' => resource_path('templates/formato_permiso.docx'),
    'descontar_vacaciones_en_estatus' => ['formato_recibido'],
    'estatus_activos_para_cruce' => [
        'formato_generado',
        'formato_enviado',
        'formato_pendiente',
        'formato_recibido',
        'con_observaciones',
    ],
    'estatus_no_activos' => ['cancelado'],
];
