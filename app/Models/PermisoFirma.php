<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoFirma extends Model
{
    protected $table = 'permiso_firmas';

    protected $fillable = [
        'permiso_solicitud_id',
        'empleado_id',
        'tipo_firma',
        'nombre',
        'correo',
        'token',
        'estatus',
        'firma_path',
        'firmado_at',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'firmado_at' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(PermisoSolicitud::class, 'permiso_solicitud_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function getFirmadoAttribute(): bool
    {
        return $this->estatus === 'firmado';
    }
}
