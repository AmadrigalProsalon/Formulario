<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoNotificacion extends Model
{
    protected $table = 'permiso_notificaciones';

    protected $fillable = [
        'permiso_solicitud_id',
        'correo',
        'tipo',
        'estatus',
        'enviado_at',
        'error',
    ];

    protected $casts = [
        'enviado_at' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(PermisoSolicitud::class, 'permiso_solicitud_id');
    }
}
