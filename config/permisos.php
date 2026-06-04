<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Correo RH
    |--------------------------------------------------------------------------
    | A este correo se enviará copia del formato inicial y el formato final
    | firmado cuando el colaborador y su líder hayan firmado.
    |
    | Si no configuras PERMISOS_RH_EMAIL, usa RH_FORM_MAIL_TO como respaldo.
    */
    'rh_email' => env('PERMISOS_RH_EMAIL', env('RH_FORM_MAIL_TO', 'rh@prosalon.mx')),

    /*
    |--------------------------------------------------------------------------
    | Plantilla Word para permisos
    |--------------------------------------------------------------------------
    | Por defecto usa resources/templates/formato_permiso.docx, que viene dentro
    | de este parche. Así no necesitas agregar rutas manuales al .env.
    |
    | Si quieres usar otra plantilla, configura PERMISOS_TEMPLATE_PATH.
    */
    'template_path' => env('PERMISOS_TEMPLATE_PATH', base_path('resources/templates/formato_permiso.docx')),

    /*
    |--------------------------------------------------------------------------
    | Disco de almacenamiento
    |--------------------------------------------------------------------------
    | Se recomienda public para poder descargar documentos desde el admin.
    */
    'disk' => env('PERMISOS_DOCUMENTOS_DISK', 'public'),

    'estatus_firma_firmado' => 'firmado',

    'firmas_requeridas' => [
        'colaborador',
        'lider',
    ],
];
