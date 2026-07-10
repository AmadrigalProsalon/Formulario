<?php

namespace App\Services\Permisos;

use App\Models\DiaInhabil;
use App\Models\Empleado;
use App\Models\VacacionesDiaInhabil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CalendarioLaboralService
{
    private const NOMBRES_DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function diasLaboralesEmpleado(Empleado $empleado): array
    {
        $empleado->loadMissing('area');

        $diasEmpleado = $this->normalizarDias($empleado->dias_laborales ?? null);
        if ($diasEmpleado) {
            return $diasEmpleado;
        }

        $diasArea = $this->normalizarDias($empleado->area?->dias_laborales ?? null);
        if ($diasArea) {
            return $diasArea;
        }

        return [1, 2, 3, 4, 5];
    }

    public function descripcionHorario(Empleado $empleado): string
    {
        $dias = $this->diasLaboralesEmpleado($empleado);

        return collect($dias)
            ->map(fn (int $dia) => self::NOMBRES_DIAS[$dia] ?? (string) $dia)
            ->implode(', ');
    }

    public function validarFechas(Empleado $empleado, array $fechas): array
    {
        $fechasNormalizadas = $this->normalizarFechas($fechas);
        $diasLaborales = $this->diasLaboralesEmpleado($empleado);
        $festivos = $this->festivosPorFecha($fechasNormalizadas);

        $validas = [];
        $invalidas = [];

        foreach ($fechasNormalizadas as $fechaTexto) {
            $fecha = Carbon::parse($fechaTexto)->startOfDay();
            $diaSemana = (int) $fecha->isoWeekday();

            if (! in_array($diaSemana, $diasLaborales, true)) {
                $invalidas[] = [
                    'fecha' => $fechaTexto,
                    'fecha_formato' => $fecha->format('d/m/Y'),
                    'motivo' => 'No es día laboral para este colaborador. Horario: ' . $this->descripcionHorario($empleado) . '.',
                ];
                continue;
            }

            if (isset($festivos[$fechaTexto])) {
                $invalidas[] = [
                    'fecha' => $fechaTexto,
                    'fecha_formato' => $fecha->format('d/m/Y'),
                    'motivo' => 'Día inhábil o festivo: ' . $festivos[$fechaTexto] . '.',
                ];
                continue;
            }

            $validas[] = $fechaTexto;
        }

        return [
            'validas' => $validas,
            'invalidas' => $invalidas,
            'dias_laborales' => $diasLaborales,
            'horario' => $this->descripcionHorario($empleado),
        ];
    }

    public function normalizarFechas(array $fechas): array
    {
        $resultado = [];

        foreach ($fechas as $fecha) {
            try {
                $resultado[] = Carbon::parse($fecha)->format('Y-m-d');
            } catch (\Throwable) {
                // La validación de Request reportará fechas inválidas.
            }
        }

        $resultado = array_values(array_unique($resultado));
        sort($resultado);

        return $resultado;
    }

    private function normalizarDias(mixed $valor): array
    {
        if (is_string($valor)) {
            $decodificado = json_decode($valor, true);
            $valor = is_array($decodificado) ? $decodificado : [];
        }

        if (! is_array($valor)) {
            return [];
        }

        $dias = array_values(array_unique(array_filter(
            array_map('intval', $valor),
            fn (int $dia) => $dia >= 1 && $dia <= 7
        )));

        return $dias;
    }

    private function festivosPorFecha(array $fechas): array
    {
        if (! $fechas) {
            return [];
        }

        $festivos = [];

        if (Schema::hasTable('dias_inhabiles')) {
            DiaInhabil::query()
                ->where('activo', true)
                ->whereIn('fecha', $fechas)
                ->get(['fecha', 'nombre'])
                ->each(function ($dia) use (&$festivos) {
                    $festivos[$dia->fecha->format('Y-m-d')] = $dia->nombre;
                });
        }

        if (Schema::hasTable('vacaciones_dias_inhabiles')) {
            VacacionesDiaInhabil::query()
                ->where('activo', true)
                ->whereIn('fecha', $fechas)
                ->get(['fecha', 'nombre'])
                ->each(function ($dia) use (&$festivos) {
                    $key = $dia->fecha->format('Y-m-d');
                    $festivos[$key] = $festivos[$key] ?? $dia->nombre;
                });
        }

        return $festivos;
    }
}
