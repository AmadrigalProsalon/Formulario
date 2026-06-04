<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }

    public function lideres()
    {
        return $this->hasMany(Empleado::class)->where('es_lider', true);
    }
}
