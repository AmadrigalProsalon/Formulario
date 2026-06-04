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
        'formato_recibido',
        'formato_recibido_at',
        'formato_recibido_por',
        'observaciones_rh',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'dias_solicitados' => 'decimal:2',
        'formato_recibido' => 'boolean',
        'formato_recibido_at' => 'datetime',
    ];

    public function tipoPermiso()
    {
        return $this->belongsTo(TipoPermiso::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function lider()
    {
        return $this->belongsTo(Empleado::class, 'lider_id');
    }

    public function firmas()
    {
        return $this->hasMany(PermisoFirma::class);
    }

    public function notificaciones()
    {
        return $this->hasMany(PermisoNotificacion::class);
    }

    public function recibidoPor()
    {
        return $this->belongsTo(User::class, 'formato_recibido_por');
    }

    public function firmasPendientes()
    {
        return $this->firmas()->where('estatus', 'pendiente');
    }

    public function getEstatusLabelAttribute(): string
    {
        return match ($this->estatus) {
            'pendiente_firma_colaborador' => 'Pendiente firma colaborador',
            'pendiente_firma_lider' => 'Pendiente firma líder',
            'firmado_completo' => 'Firmado completo',
            'formato_recibido' => 'Formato recibido RH',
            'formato_pendiente' => 'Formato pendiente RH',
            'con_observaciones' => 'Con observaciones',
            'cancelado' => 'Cancelado',
            default => ucfirst(str_replace('_', ' ', (string) $this->estatus)),
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->estatus) {
            'pendiente_firma_colaborador', 'pendiente_firma_lider' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'firmado_completo' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'formato_recibido' => 'bg-blue-100 text-blue-800 border-blue-200',
            'formato_pendiente' => 'bg-slate-100 text-slate-800 border-slate-200',
            'con_observaciones' => 'bg-orange-100 text-orange-800 border-orange-200',
            'cancelado' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
