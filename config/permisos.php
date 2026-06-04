<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Correo RH
    |--------------------------------------------------------------------------
    | A este correo se enviará copia del formato inicial y el formato final
    | firmado cuando el colaborador y su líder hayan firmado.
    */
    'rh_email' => env('PERMISOS_RH_EMAIL', env('RH_FORM_MAIL_TO')),

    /*
    |--------------------------------------------------------------------------
    | Plantilla Word para permisos
    |--------------------------------------------------------------------------
    | Puedes subir tu plantilla a storage/app/templates/formato_permiso.docx.
    | Si no existe, el sistema generará un DOCX básico automáticamente.
    */
    'template_path' => env('PERMISOS_TEMPLATE_PATH', storage_path('app/templates/formato_permiso.docx')),

    /*
    |--------------------------------------------------------------------------
    | Disco de almacenamiento
    |--------------------------------------------------------------------------
    | Se recomienda public para poder descargar documentos desde el admin.
    */
    'disk' => env('PERMISOS_DOCUMENTOS_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Estados de firmas
    |--------------------------------------------------------------------------
    */
    'estatus_firma_firmado' => 'firmado',

    /*
    |--------------------------------------------------------------------------
    | Tipos de firma requeridos
    |--------------------------------------------------------------------------
    */
    'firmas_requeridas' => [
        'colaborador',
        'lider',
    ],
];
