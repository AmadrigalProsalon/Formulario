<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacacionesSolicitud extends Model
{
    protected $table = 'vacaciones_solicitudes';

    protected $fillable = [
        'empleado_id',
        'formulario_id',
        'respuesta_id',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'estatus',
        'comentarios_empleado',
        'comentarios_admin',
        'aprobado_por',
        'aprobado_at',
        'rechazado_at',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'dias_solicitados' => 'decimal:2',
        'aprobado_at' => 'datetime',
        'rechazado_at' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }

    public function respuesta()
    {
        return $this->belongsTo(Respuesta::class, 'respuesta_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
