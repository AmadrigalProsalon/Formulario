<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $table = 'respuestas';

    protected $fillable = [
        'formulario_id',
        'departamento',
        'puesto',
        'horario',
        'data',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }
}
