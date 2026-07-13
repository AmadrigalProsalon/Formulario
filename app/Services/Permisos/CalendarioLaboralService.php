<?php

namespace App\Services\Permisos;

use App\Models\DiaInhabil;
use App\Models\Empleado;
use App\Models\VacacionesDiaInhabil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        return $this->resolverHorario($empleado)['dias'];
    }

    public function descripcionHorario(Empleado $empleado): string
    {
        $dias = $this->diasLaboralesEmpleado($empleado);

        return collect($dias)
            ->map(fn (int $dia) => self::NOMBRES_DIAS[$dia] ?? (string) $dia)
            ->implode(', ');
    }

    public function origenHorario(Empleado $empleado): string
    {
        return $this->resolverHorario($empleado)['origen'];
    }

    public function validarFechas(Empleado $empleado, array $fechas): array
    {
        $fechasNormalizadas = $this->normalizarFechas($fechas);
        $resolucion = $this->resolverHorario($empleado);
        $diasLaborales = $resolucion['dias'];
        $festivos = $this->festivosPorFecha($fechasNormalizadas);

        $validas = [];
        $invalidas = [];

        foreach ($fechasNormalizadas as $fechaTexto) {
            $fecha = Carbon::parse($fechaTexto)->startOfDay();
            $diaSemana = (int) $fecha->isoWeekday();

            if (isset($festivos[$fechaTexto])) {
                $invalidas[] = [
                    'fecha' => $fechaTexto,
                    'fecha_formato' => $fecha->format('d/m/Y'),
                    'motivo' => 'Día inhábil o festivo: ' . $festivos[$fechaTexto] . '.',
                ];
                continue;
            }

            if (! in_array($diaSemana, $diasLaborales, true)) {
                $invalidas[] = [
                    'fecha' => $fechaTexto,
                    'fecha_formato' => $fecha->format('d/m/Y'),
                    'motivo' => 'No es día laboral para este colaborador. Horario: ' . $this->descripcionHorario($empleado) . '.',
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
            'origen_horario' => $resolucion['origen'],
        ];
    }

    public function normalizarFechas(array $fechas): array
    {
        $resultado = [];

        foreach ($fechas as $fecha) {
            try {
                $resultado[] = Carbon::parse($fecha)->format('Y-m-d');
            } catch (\Throwable) {
                // La validación HTTP es la responsable de reportar formatos inválidos.
            }
        }

        $resultado = array_values(array_unique($resultado));
        sort($resultado);

        return $resultado;
    }

    private function resolverHorario(Empleado $empleado): array
    {
        $empleado->loadMissing('area');

        $diasEmpleado = $this->normalizarDias($empleado->dias_laborales ?? null);
        if ($diasEmpleado) {
            return ['dias' => $diasEmpleado, 'origen' => 'Horario especial del empleado'];
        }

        $nombreEmpleado = $this->normalizarTexto($empleado->nombre);

        $diasReglaEmpleado = $this->buscarRegla(
            $nombreEmpleado,
            (array) config('calendario_laboral.reglas_empleados', [])
        );

        if ($diasReglaEmpleado) {
            return ['dias' => $diasReglaEmpleado, 'origen' => 'Regla especial del empleado'];
        }

        $nombreArea = $this->normalizarTexto($empleado->area?->nombre);

        if (str_contains($nombreArea, 'punta mita')) {
            $diasPuntaMita = $this->buscarRegla(
                $nombreEmpleado,
                (array) config('calendario_laboral.reglas_punta_mita', [])
            );

            if ($diasPuntaMita) {
                return ['dias' => $diasPuntaMita, 'origen' => 'Regla especial de Punta Mita'];
            }
        }

        $diasArea = $this->normalizarDias($empleado->area?->dias_laborales ?? null);
        if ($diasArea) {
            return ['dias' => $diasArea, 'origen' => 'Horario configurado para el área'];
        }

        $diasReglaArea = $this->buscarRegla(
            $nombreArea,
            (array) config('calendario_laboral.reglas_areas', [])
        );

        if ($diasReglaArea) {
            return ['dias' => $diasReglaArea, 'origen' => 'Regla del área'];
        }

        return [
            'dias' => $this->normalizarDias(config('calendario_laboral.dias_por_defecto', [1, 2, 3, 4, 5])),
            'origen' => 'Horario por defecto',
        ];
    }

    private function buscarRegla(string $texto, array $reglas): array
    {
        foreach ($reglas as $fragmento => $dias) {
            $fragmentoNormalizado = $this->normalizarTexto((string) $fragmento);

            if ($fragmentoNormalizado !== '' && str_contains($texto, $fragmentoNormalizado)) {
                return $this->normalizarDias($dias);
            }
        }

        return [];
    }

    private function normalizarTexto(?string $texto): string
    {
        return Str::lower(Str::ascii(trim((string) $texto)));
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

        sort($dias);

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
