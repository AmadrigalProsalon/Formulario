<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacacionesDiaInhabil extends Model
{
    protected $table = 'vacaciones_dias_inhabiles';

    protected $fillable = [
        'fecha',
        'nombre',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'activo' => 'boolean',
    ];
}
