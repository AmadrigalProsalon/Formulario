<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';

    protected $fillable = [
        'numero_empleado',
        'nombre',
        'correo',
        'departamento',
        'puesto',
        'fecha_ingreso',
        'activo',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'activo' => 'boolean',
    ];

    public function solicitudesVacaciones()
    {
        return $this->hasMany(VacacionesSolicitud::class, 'empleado_id');
    }

    public function ajustesVacaciones()
    {
        return $this->hasMany(VacacionesAjuste::class, 'empleado_id');
    }
}
