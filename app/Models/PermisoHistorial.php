<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoHistorial extends Model
{
    protected $table = 'permisos_historial';

    protected $fillable = [
        'permiso_solicitud_id',
        'user_id',
        'accion',
        'descripcion',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function permiso()
    {
        return $this->belongsTo(PermisoSolicitud::class, 'permiso_solicitud_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
