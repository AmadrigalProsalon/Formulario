<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $table = 'respuestas';

    protected $fillable = [
        'departamento',
        'puesto',
        'horario',
        'data'
    ];

    protected $casts = [
        'data' => 'array', // 🔥 convierte JSON automáticamente a array
    ];
}
