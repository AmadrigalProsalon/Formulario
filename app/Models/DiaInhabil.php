<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaInhabil extends Model
{
    protected $table = 'dias_inhabiles';

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
