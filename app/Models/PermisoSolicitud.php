<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoSolicitud extends Model
{
    protected $table = 'permisos_solicitudes';

    protected $fillable = [
        'tipo_permiso_id',
        'empleado_id',
        'area_id',
        'lider_id',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'motivo',
        'estatus',
        'documento_path',
        'archivo_firmado_path',
        'archivo_firmado_original',
        'archivo_firmado_at',
        'archivo_firmado_por',
        'documento_enviado_at',
        'formato_recibido',
        'formato_recibido_at',
        'formato_recibido_por',
        'observaciones_rh',
        'cancelado_at',
        'cancelado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'documento_enviado_at' => 'datetime',
        'archivo_firmado_at' => 'datetime',
        'formato_recibido' => 'boolean',
        'formato_recibido_at' => 'datetime',
        'cancelado_at' => 'datetime',
        'dias_solicitados' => 'decimal:2',
    ];


    public function diasSeleccionados()
    {
        return $this->hasMany(PermisoSolicitudDia::class, 'permiso_solicitud_id')->orderBy('fecha');
    }

    public function tipoPermiso()
    {
        return $this->belongsTo(TipoPermiso::class, 'tipo_permiso_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function lider()
    {
        return $this->belongsTo(Empleado::class, 'lider_id');
    }

    public function recibidoPor()
    {
        return $this->belongsTo(User::class, 'formato_recibido_por');
    }

    public function archivoFirmadoPor()
    {
        return $this->belongsTo(User::class, 'archivo_firmado_por');
    }

    public function historial()
    {
        return $this->hasMany(PermisoHistorial::class, 'permiso_solicitud_id')->latest();
    }

    public function scopeActivas($query)
    {
        return $query->whereNotIn('estatus', config('permisos.estatus_no_activos', ['cancelado']));
    }

    public static function existeCruceDeFechas(int $empleadoId, string $fechaInicio, string $fechaFin, ?int $ignorarSolicitudId = null): ?self
    {
        return self::where('empleado_id', $empleadoId)
            ->when($ignorarSolicitudId, fn ($q) => $q->where('id', '!=', $ignorarSolicitudId))
            ->whereIn('estatus', config('permisos.estatus_activos_para_cruce', [
                'formato_generado',
                'formato_enviado',
                'formato_pendiente',
                'formato_recibido',
                'con_observaciones',
            ]))
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereDate('fecha_inicio', '<=', $fechaFin)
                    ->whereDate('fecha_fin', '>=', $fechaInicio);
            })
            ->first();
    }
}
