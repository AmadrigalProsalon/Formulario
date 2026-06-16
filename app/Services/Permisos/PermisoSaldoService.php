<?php

namespace App\Services\Permisos;

use App\Models\Empleado;
use App\Models\PermisoSolicitud;
use App\Models\TipoPermiso;
use Carbon\Carbon;

class PermisoSaldoService
{
    public function diasVacacionesCorrespondientes(?Carbon $fechaIngreso): int
    {
        if (! $fechaIngreso) {
            return 0;
        }

        $anios = (int) $fechaIngreso->diffInYears(now());

        if ($anios < 1) {
            return 0;
        }

        return match (true) {
            $anios === 1 => 12,
            $anios === 2 => 14,
            $anios === 3 => 16,
            $anios === 4 => 18,
            $anios === 5 => 20,
            default => 20 + (int) (floor(($anios - 6) / 5) + 1) * 2,
        };
    }

    public function diasUsados(Empleado $empleado): float
    {
        return (float) PermisoSolicitud::where('empleado_id', $empleado->id)
            ->whereHas('tipoPermiso', fn ($q) => $q->where('descuenta_vacaciones', true))
            ->whereIn('estatus', config('permisos.descontar_vacaciones_en_estatus', ['formato_recibido']))
            ->sum('dias_solicitados');
    }

    public function diasPendientes(Empleado $empleado): float
    {
        return (float) PermisoSolicitud::where('empleado_id', $empleado->id)
            ->whereHas('tipoPermiso', fn ($q) => $q->where('descuenta_vacaciones', true))
            ->whereIn('estatus', ['formato_generado', 'formato_enviado', 'formato_pendiente', 'con_observaciones'])
            ->sum('dias_solicitados');
    }

    public function resumen(Empleado $empleado): array
    {
        $correspondientes = $this->diasVacacionesCorrespondientes($empleado->fecha_ingreso);
        $usados = $this->diasUsados($empleado);
        $pendientes = $this->diasPendientes($empleado);
        $ajuste = (float) ($empleado->vacaciones_ajuste ?? 0);
        $total = max(0, $correspondientes + $ajuste);

        return [
            'fecha_ingreso' => $empleado->fecha_ingreso?->format('Y-m-d'),
            'fecha_ingreso_formato' => $empleado->fecha_ingreso?->format('d/m/Y'),
            'dias_correspondientes' => $correspondientes,
            'dias_ajuste' => $ajuste,
            'dias_asignados_total' => $total,
            'dias_usados' => $usados,
            'dias_pendientes_formato' => $pendientes,
            // Regla actual: pendientes no descuentan. Solo descuentan los recibidos por RH.
            'dias_disponibles' => max(0, $total - $usados),
            'dias_restantes' => max(0, $total - $usados),
        ];
    }

    public function validarSaldoSuficiente(Empleado $empleado, TipoPermiso $tipoPermiso, float $diasSolicitados): bool
    {
        if (! $tipoPermiso->requiere_saldo && ! $tipoPermiso->descuenta_vacaciones) {
            return true;
        }

        return $diasSolicitados <= $this->resumen($empleado)['dias_disponibles'];
    }
}
