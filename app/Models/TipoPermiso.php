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
        'requiere_firma_colaborador',
        'requiere_firma_lider',
        'requiere_recepcion_rh',
        'activo',
    ];

    protected $casts = [
        'descuenta_vacaciones' => 'boolean',
        'requiere_saldo' => 'boolean',
        'requiere_firma_colaborador' => 'boolean',
        'requiere_firma_lider' => 'boolean',
        'requiere_recepcion_rh' => 'boolean',
        'activo' => 'boolean',
    ];

    public function solicitudes()
    {
        return $this->hasMany(PermisoSolicitud::class);
    }
}
