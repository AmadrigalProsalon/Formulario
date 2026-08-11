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
        'vacaciones_ganadas_base',
        'vacaciones_saldo_anterior_base',
        'vacaciones_saldo_actual_base',
        'vacaciones_anio_base',
        'vacaciones_fecha_vencimiento',
        'vacaciones_fecha_corte',
        'dias_laborales',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'es_lider' => 'boolean',
        'activo' => 'boolean',
        'vacaciones_ajuste' => 'decimal:2',
        'vacaciones_usados' => 'decimal:2',
        'vacaciones_pendientes' => 'decimal:2',
        'vacaciones_ganadas_base' => 'decimal:4',
        'vacaciones_saldo_anterior_base' => 'decimal:4',
        'vacaciones_saldo_actual_base' => 'decimal:4',
        'vacaciones_anio_base' => 'integer',
        'vacaciones_fecha_vencimiento' => 'date',
        'vacaciones_fecha_corte' => 'date',
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


    public function getVacacionesDisponiblesAttribute(): float
    {
        return (float) app(\App\Services\Permisos\PermisoSaldoService::class)
            ->resumen($this)['dias_disponibles'];
    }

    public function getEtiquetaAttribute(): string
    {
        return trim(($this->numero_empleado ? $this->numero_empleado . ' - ' : '') . $this->nombre);
    }
}
