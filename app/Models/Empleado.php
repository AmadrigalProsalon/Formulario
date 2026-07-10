<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';

    protected $fillable = [
        'area_id',
        'numero_empleado',
        'curp',
        'rfc',
        'nombre',
        'correo',
        'puesto',
        'fecha_ingreso',
        'es_lider',
        'lider_id',
        'activo',
        'vacaciones_ajuste',
        'vacaciones_usados',
        'vacaciones_pendientes',
        'dias_laborales',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'es_lider' => 'boolean',
        'activo' => 'boolean',
        'vacaciones_ajuste' => 'decimal:2',
        'vacaciones_usados' => 'decimal:2',
        'vacaciones_pendientes' => 'decimal:2',
        'dias_laborales' => 'array',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function lider()
    {
        return $this->belongsTo(self::class, 'lider_id');
    }

    public function colaboradores()
    {
        return $this->hasMany(self::class, 'lider_id');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoSolicitud::class, 'empleado_id');
    }

    public function getEtiquetaAttribute(): string
    {
        return trim(($this->numero_empleado ? $this->numero_empleado . ' - ' : '') . $this->nombre);
    }
}
