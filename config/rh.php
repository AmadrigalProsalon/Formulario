<?php

return [
    'mail_to' => env('RH_FORM_MAIL_TO', 'amadrigal@prosalon.mx'),

    'template_path' => env(
        'RH_WORD_TEMPLATE_PATH',
        storage_path('app/templates/plantilla.docx')
    ),

    'admin' => [
        'name' => env('RH_ADMIN_NAME', 'Administrador RH'),
        'email' => env('RH_ADMIN_EMAIL', 'amadrigal@prosalon.mx'),
        'password' => env('RH_ADMIN_PASSWORD', 'Cambiar123456!'),
    ],
];
