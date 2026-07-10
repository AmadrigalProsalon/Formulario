<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';

    protected $fillable = ['nombre', 'descripcion', 'activo', 'dias_laborales'];

    protected $casts = [
        'activo' => 'boolean',
        'dias_laborales' => 'array',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'area_id');
    }
}
