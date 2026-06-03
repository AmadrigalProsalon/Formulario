<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\VacacionesAjuste;
use App\Models\VacacionesDiaInhabil;
use App\Models\VacacionesSolicitud;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class VacacionesService
{
    public function diasPorAntiguedad(Empleado $empleado, ?int $anio = null): float
    {
        $anio = $anio ?: now()->year;

        if (! $empleado->fecha_ingreso) {
            return 0;
        }

        $fechaCorte = Carbon::create($anio, 12, 31);
        $anios = $empleado->fecha_ingreso->diffInYears($fechaCorte);

        if ($anios < 1) {
            return 0;
        }

        if ($anios <= 5) {
            return 10 + ($anios * 2); // año 1=12, 2=14, 3=16, 4=18, 5=20
        }

        return 20 + (floor(($anios - 5) / 5) * 2);
    }

    public function resumen(Empleado $empleado, ?int $anio = null): array
    {
        $anio = $anio ?: now()->year;

        $diasCorrespondientes = $this->diasPorAntiguedad($empleado, $anio);

        $diasAjuste = (float) VacacionesAjuste::where('empleado_id', $empleado->id)
            ->where('anio', $anio)
            ->sum('dias');

        $diasUsados = (float) VacacionesSolicitud::where('empleado_id', $empleado->id)
            ->where('estatus', 'aprobada')
            ->whereYear('fecha_inicio', $anio)
            ->sum('dias_solicitados');

        $diasPendientes = (float) VacacionesSolicitud::where('empleado_id', $empleado->id)
            ->where('estatus', 'pendiente')
            ->whereYear('fecha_inicio', $anio)
            ->sum('dias_solicitados');

        $total = $diasCorrespondientes + $diasAjuste;
        $disponibles = max(0, $total - $diasUsados - $diasPendientes);

        return [
            'anio' => $anio,
            'dias_correspondientes' => round($diasCorrespondientes, 2),
            'dias_ajuste' => round($diasAjuste, 2),
            'dias_totales' => round($total, 2),
            'dias_usados' => round($diasUsados, 2),
            'dias_pendientes' => round($diasPendientes, 2),
            'dias_disponibles' => round($disponibles, 2),
        ];
    }

    public function calcularDiasLaborables(string $fechaInicio, string $fechaFin): float
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();

        if ($fin->lt($inicio)) {
            return 0;
        }

        $inhabiles = VacacionesDiaInhabil::where('activo', true)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->pluck('fecha')
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
            ->toArray();

        $dias = 0;

        foreach (CarbonPeriod::create($inicio, $fin) as $fecha) {
            if ($fecha->isWeekend()) {
                continue;
            }

            if (in_array($fecha->toDateString(), $inhabiles, true)) {
                continue;
            }

            $dias++;
        }

        return (float) $dias;
    }

    public function crearSolicitud(Empleado $empleado, array $data): VacacionesSolicitud
    {
        return DB::transaction(function () use ($empleado, $data) {
            $diasSolicitados = $this->calcularDiasLaborables($data['fecha_inicio'], $data['fecha_fin']);
            $resumen = $this->resumen($empleado, Carbon::parse($data['fecha_inicio'])->year);

            if ($diasSolicitados <= 0) {
                throw new \RuntimeException('El rango seleccionado no contiene días laborables para descontar.');
            }

            if ($diasSolicitados > $resumen['dias_disponibles']) {
                throw new \RuntimeException('No puedes solicitar más días de los disponibles. Disponibles: ' . $resumen['dias_disponibles']);
            }

            return VacacionesSolicitud::create([
                'empleado_id' => $empleado->id,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'dias_solicitados' => $diasSolicitados,
                'estatus' => 'pendiente',
                'comentarios_empleado' => $data['comentarios_empleado'] ?? null,
            ]);
        });
    }
}
