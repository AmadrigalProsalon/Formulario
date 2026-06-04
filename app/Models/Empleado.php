<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $fillable = [
        'area_id',
        'lider_id',
        'numero_empleado',
        'nombre',
        'correo',
        'puesto',
        'fecha_ingreso',
        'es_lider',
        'activo',
        'vacaciones_ajuste',
        'vacaciones_usados',
        'vacaciones_pendientes',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'es_lider' => 'boolean',
        'activo' => 'boolean',
        'vacaciones_ajuste' => 'decimal:2',
        'vacaciones_usados' => 'decimal:2',
        'vacaciones_pendientes' => 'decimal:2',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function lider()
    {
        return $this->belongsTo(Empleado::class, 'lider_id');
    }

    public function colaboradores()
    {
        return $this->hasMany(Empleado::class, 'lider_id');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoSolicitud::class);
    }

    public function getAntiguedadAniosAttribute(): int
    {
        if (! $this->fecha_ingreso) {
            return 0;
        }

        return max(0, Carbon::parse($this->fecha_ingreso)->diffInYears(now()));
    }

    public function getVacacionesLeyAttribute(): float
    {
        $anios = $this->antiguedad_anios;

        if ($anios < 1) {
            return 0;
        }

        if ($anios <= 5) {
            return 12 + (($anios - 1) * 2);
        }

        return 20 + (ceil(($anios - 5) / 5) * 2);
    }

    public function getVacacionesTotalesAttribute(): float
    {
        return (float) $this->vacaciones_ley + (float) $this->vacaciones_ajuste;
    }

    public function getVacacionesDisponiblesAttribute(): float
    {
        return max(0, (float) $this->vacaciones_totales - (float) $this->vacaciones_usados - (float) $this->vacaciones_pendientes);
    }
}
