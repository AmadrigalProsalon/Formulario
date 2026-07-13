<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Calendario laboral de vacaciones
    |--------------------------------------------------------------------------
    |
    | Los números usan ISO-8601:
    | 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves,
    | 5=Viernes, 6=Sábado, 7=Domingo.
    |
    | Prioridad:
    | 1) horario especial guardado en el empleado;
    | 2) regla especial por nombre;
    | 3) regla especial de Punta Mita;
    | 4) horario guardado en el área;
    | 5) regla por nombre de área;
    | 6) horario por defecto.
    */

    'dias_por_defecto' => [1, 2, 3, 4, 5],

    'reglas_areas' => [
        'almacen vespertino' => [7, 1, 2, 3, 4, 5],
        'almacen matutino' => [1, 2, 3, 4, 5, 6],
        'almacenistas acatlan' => [1, 2, 3, 4, 5],
        'almacenista acatlan' => [1, 2, 3, 4, 5],
        'oficinas' => [1, 2, 3, 4, 5],
        'oficina' => [1, 2, 3, 4, 5],
        'acatlan' => [1, 2, 3, 4, 5],
    ],

    'reglas_empleados' => [
        'ricardo baltazar' => [5, 6, 7],
        'miguel corona' => [1, 2, 3, 4],
        'jose sanabria' => [1, 2, 3, 4, 5, 6],
        'antonio fernandez' => [1, 2, 3, 4, 5, 6],
        'juan jose' => [1, 2, 3, 4, 5],
        'oscar ivan' => [5, 6, 7, 1],
        'victor manuel santos' => [1, 2, 3, 4, 5, 6],
        'jesus cardenas' => [1, 2, 3, 4, 5, 6],
        'cesar alejandro rodriguez' => [1, 2, 3, 4, 5, 6],
        'alejandro pantoja' => [1, 2, 3, 4, 5, 6],
        'isabel' => [1, 2, 3, 4, 5, 6],
        'christoper rosales' => [7, 1, 2, 3, 4, 5],
    ],

    'reglas_punta_mita' => [
        'dulce' => [3, 4, 5, 6, 7, 1],
        'delfina' => [1, 2, 3, 4, 5],
        'lizeth' => [5, 6, 7],
        'noemi' => [1, 2, 3, 4, 5, 6],
        'cecilia' => [4, 5, 6, 7, 1, 2],
        'jose' => [1, 2, 3, 4, 5, 6],
        'valentin' => [5, 6, 7, 1, 2, 3],
        'elsa' => [2, 3, 4, 5, 6, 7],
        'angel' => [4, 5, 6, 7, 1, 2],
        'cesar' => [3, 4, 5, 6, 7, 1],
        'saul' => [5, 6, 7, 1, 2, 3],
    ],
];
