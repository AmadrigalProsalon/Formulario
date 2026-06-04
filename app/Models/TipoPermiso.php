<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPermiso extends Model
{
    protected $table = 'tipos_permisos';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'descuenta_vacaciones',
        'requiere_saldo',
        'activo',
    ];

    protected $casts = [
        'descuenta_vacaciones' => 'boolean',
        'requiere_saldo' => 'boolean',
        'activo' => 'boolean',
    ];
}
