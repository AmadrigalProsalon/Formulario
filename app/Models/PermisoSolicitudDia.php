<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoSolicitudDia extends Model
{
    protected $table = 'permiso_solicitud_dias';

    protected $fillable = [
        'permiso_solicitud_id',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function solicitud()
    {
        return $this->belongsTo(PermisoSolicitud::class, 'permiso_solicitud_id');
    }
}
