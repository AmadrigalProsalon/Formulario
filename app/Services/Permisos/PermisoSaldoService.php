<?php

namespace App\Services\Permisos;

use App\Models\Empleado;
use App\Models\PermisoSolicitud;
use App\Models\TipoPermiso;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PermisoSaldoService
{
    public function diasVacacionesCorrespondientes(?Carbon $fechaIngreso, ?Carbon $fechaReferencia = null): int
    {
        if (! $fechaIngreso) {
            return 0;
        }

        $fechaReferencia ??= now();
        $aniosCumplidos = max(0, (int) $fechaIngreso->copy()->startOfDay()->diffInYears($fechaReferencia->copy()->startOfDay()));
        $anioServicio = $aniosCumplidos + 1;

        return match (true) {
            $anioServicio <= 1 => 12,
            $anioServicio === 2 => 14,
            $anioServicio === 3 => 16,
            $anioServicio === 4 => 18,
            $anioServicio === 5 => 20,
            default => 20 + (int) (floor(($anioServicio - 6) / 5) + 1) * 2,
        };
    }

    public function proporcionalGeneradoEnAnio(?Carbon $fechaIngreso, Carbon $hasta): float
    {
        if (! $fechaIngreso || $fechaIngreso->gt($hasta)) {
            return 0.0;
        }

        $inicio = Carbon::create($hasta->year, 1, 1)->startOfDay();
        if ($fechaIngreso->gt($inicio)) {
            $inicio = $fechaIngreso->copy()->startOfDay();
        }

        return $this->acumularProporcional($fechaIngreso, $inicio, $hasta);
    }

    public function proporcionalGenerado(Empleado $empleado, ?Carbon $hasta = null): float
    {
        $fechaIngreso = $empleado->fecha_ingreso?->copy()->startOfDay();
        $fechaCorte = $empleado->vacaciones_fecha_corte?->copy()->startOfDay();
        $hasta = ($hasta ?? now())->copy()->startOfDay();

        if (! $fechaIngreso || ! $fechaCorte || $fechaCorte->gte($hasta)) {
            return 0.0;
        }

        return $this->acumularProporcional($fechaIngreso, $fechaCorte->copy()->addDay(), $hasta);
    }

    private function acumularProporcional(Carbon $fechaIngreso, Carbon $desde, Carbon $hasta): float
    {
        if ($desde->gt($hasta)) {
            return 0.0;
        }

        $fecha = $desde->copy();
        if ($fecha->lt($fechaIngreso)) {
            $fecha = $fechaIngreso->copy();
        }

        $generado = 0.0;
        while ($fecha->lte($hasta)) {
            $diasAnuales = $this->diasVacacionesCorrespondientes($fechaIngreso, $fecha);
            $generado += $diasAnuales / ($fecha->isLeapYear() ? 366 : 365);
            $fecha->addDay();
        }

        return round($generado, 4);
    }

    /**
     * Simula las dos bolsas: saldo del año anterior (vence el 30 de abril)
     * y proporcional del año actual. Cada día solicitado consume primero la
     * bolsa más antigua. Los días salteados se evalúan uno por uno.
     */
    public function simular(Empleado $empleado, array $fechasCandidatas = [], ?Carbon $hasta = null): array
    {
        $corte = $empleado->vacaciones_fecha_corte?->copy()->startOfDay() ?? now()->startOfDay();
        $fechaIngreso = $empleado->fecha_ingreso?->copy()->startOfDay();

        $anterior = max(0.0, (float) ($empleado->vacaciones_saldo_anterior_base ?? 0));
        $actual = max(0.0, (float) ($empleado->vacaciones_saldo_actual_base ?? $empleado->vacaciones_ganadas_base ?? 0));
        $anioActual = (int) ($empleado->vacaciones_anio_base ?: $corte->year);

        // Compatibilidad con empleados importados antes de esta versión.
        if ((float) $anterior === 0.0 && (float) $actual === 0.0 && (float) ($empleado->vacaciones_ganadas_base ?? 0) > 0) {
            $actual = (float) $empleado->vacaciones_ganadas_base;
        }

        $eventos = $this->fechasReservadas($empleado)
            ->merge(collect($fechasCandidatas)->map(fn ($f) => Carbon::parse($f)->format('Y-m-d')))
            ->filter(fn ($f) => Carbon::parse($f)->gt($corte))
            ->countBy()
            ->sortKeys();

        $fin = ($hasta ?? now())->copy()->startOfDay();
        if ($eventos->isNotEmpty()) {
            $ultima = Carbon::parse($eventos->keys()->last());
            if ($ultima->gt($fin)) {
                $fin = $ultima;
            }
        }

        $cursor = $corte->copy();
        $deficit = 0.0;
        $consumidoAnterior = 0.0;
        $consumidoActual = 0.0;
        $vencido = 0.0;

        foreach ($eventos as $fechaTexto => $cantidad) {
            $fechaEvento = Carbon::parse($fechaTexto)->startOfDay();
            $this->avanzarBolsas($fechaIngreso, $cursor, $fechaEvento, $anterior, $actual, $anioActual, $vencido);

            for ($i = 0; $i < $cantidad; $i++) {
                if ($anterior >= 1) {
                    $anterior -= 1;
                    $consumidoAnterior += 1;
                } elseif ($actual >= 1) {
                    $actual -= 1;
                    $consumidoActual += 1;
                } else {
                    $faltante = 1 - max(0, $anterior) - max(0, $actual);
                    $deficit += max(0, $faltante);
                    $anterior = 0;
                    $actual = 0;
                }
            }
        }

        $this->avanzarBolsas($fechaIngreso, $cursor, $fin, $anterior, $actual, $anioActual, $vencido);

        return [
            'anio_actual' => $anioActual,
            'saldo_anterior' => round(max(0, $anterior), 4),
            'saldo_actual' => round(max(0, $actual), 4),
            'disponible' => round(max(0, $anterior + $actual), 2),
            'deficit' => round($deficit, 2),
            'consumido_anterior' => round($consumidoAnterior, 2),
            'consumido_actual' => round($consumidoActual, 2),
            'vencido' => round($vencido, 2),
            'fecha_vencimiento' => Carbon::create($anioActual, 4, 30)->format('Y-m-d'),
        ];
    }

    private function avanzarBolsas(?Carbon $fechaIngreso, Carbon &$cursor, Carbon $destino, float &$anterior, float &$actual, int &$anioActual, float &$vencido): void
    {
        while ($cursor->lt($destino)) {
            $cursor->addDay();

            if ($cursor->month === 1 && $cursor->day === 1 && $cursor->year > $anioActual) {
                $anterior = $actual;
                $actual = 0.0;
                $anioActual = $cursor->year;
            }

            if ($cursor->month === 5 && $cursor->day === 1 && $anterior > 0) {
                $vencido += $anterior;
                $anterior = 0.0;
            }

            if ($fechaIngreso && $cursor->gte($fechaIngreso)) {
                $actual += $this->diasVacacionesCorrespondientes($fechaIngreso, $cursor)
                    / ($cursor->isLeapYear() ? 366 : 365);
            }
        }
    }

    private function fechasReservadas(Empleado $empleado): Collection
    {
        $solicitudes = PermisoSolicitud::with('diasSeleccionados')
            ->where('empleado_id', $empleado->id)
            ->whereHas('tipoPermiso', fn ($q) => $q->where('descuenta_vacaciones', true))
            ->whereNotIn('estatus', ['cancelado', 'rechazado'])
            ->get();

        return $solicitudes->flatMap(function (PermisoSolicitud $solicitud) {
            if ($solicitud->diasSeleccionados->isNotEmpty()) {
                return $solicitud->diasSeleccionados->map(fn ($d) => $d->fecha->format('Y-m-d'));
            }

            // Compatibilidad con solicitudes antiguas sin detalle por fecha.
            $fechas = [];
            $fecha = $solicitud->fecha_inicio?->copy();
            $restantes = (int) round((float) $solicitud->dias_solicitados);
            while ($fecha && $fecha->lte($solicitud->fecha_fin) && $restantes > 0) {
                $fechas[] = $fecha->format('Y-m-d');
                $fecha->addDay();
                $restantes--;
            }
            return $fechas;
        });
    }

    /**
     * Separa los días de vacaciones que ya fueron tomados de los que todavía
     * están apartados o pendientes. Los registros históricos y las solicitudes
     * aprobadas cuentan como tomados únicamente cuando su fecha ya ocurrió.
     */
    private function consumoVacaciones(Empleado $empleado, ?Carbon $hasta = null): array
    {
        $hasta = ($hasta ?? now())->copy()->endOfDay();

        $solicitudes = PermisoSolicitud::with('diasSeleccionados')
            ->where('empleado_id', $empleado->id)
            ->whereHas('tipoPermiso', function ($query) {
                $query->where('descuenta_vacaciones', true)
                    ->orWhere('slug', 'vacaciones');
            })
            ->whereNotIn('estatus', ['cancelado', 'rechazado'])
            ->get();

        $tomados = 0.0;
        $apartados = 0.0;

        foreach ($solicitudes as $solicitud) {
            $aprobadaOHistorica = $solicitud->esHistorica()
                || in_array($solicitud->estatus, ['formato_recibido', 'aprobada'], true)
                || (bool) $solicitud->formato_recibido;

            if ($solicitud->diasSeleccionados->isNotEmpty()) {
                foreach ($solicitud->diasSeleccionados as $dia) {
                    if ($aprobadaOHistorica && $dia->fecha && $dia->fecha->copy()->endOfDay()->lte($hasta)) {
                        $tomados += 1;
                    } else {
                        $apartados += 1;
                    }
                }

                continue;
            }

            // Compatibilidad con solicitudes antiguas que no guardaron el
            // detalle por fecha. Se reparte el total según el rango registrado.
            $total = max(0.0, (float) $solicitud->dias_solicitados);
            $inicio = $solicitud->fecha_inicio?->copy()->startOfDay();
            $fin = $solicitud->fecha_fin?->copy()->endOfDay();

            if (! $aprobadaOHistorica || ! $inicio || $inicio->gt($hasta)) {
                $apartados += $total;
                continue;
            }

            if (! $fin || $fin->lte($hasta)) {
                $tomados += $total;
                continue;
            }

            $diasRango = max(1, $inicio->diffInDays($fin->copy()->startOfDay()) + 1);
            $diasTranscurridos = max(0, $inicio->diffInDays($hasta->copy()->startOfDay()) + 1);
            $proporcionTomada = min(1, $diasTranscurridos / $diasRango);
            $tomadosSolicitud = min($total, round($total * $proporcionTomada, 2));

            $tomados += $tomadosSolicitud;
            $apartados += max(0, $total - $tomadosSolicitud);
        }

        // Algunas importaciones antiguas solo dejaron el acumulado en el
        // empleado. Se conserva como mínimo para no perder el histórico.
        $tomados = max($tomados, (float) ($empleado->vacaciones_usados ?? 0));

        return [
            'tomados' => round($tomados, 2),
            'apartados' => round($apartados, 2),
        ];
    }

    public function resumen(Empleado $empleado): array
    {
        $hoy = now()->startOfDay();
        $simulacion = $this->simular($empleado, [], $hoy);
        $baseExcel = round((float) ($empleado->vacaciones_ganadas_base ?? 0), 4);
        $proporcional = $this->proporcionalGenerado($empleado, $hoy);
        $consumo = $this->consumoVacaciones($empleado, $hoy);

        return [
            'fecha_ingreso' => $empleado->fecha_ingreso?->format('Y-m-d'),
            'fecha_ingreso_formato' => $empleado->fecha_ingreso?->format('d/m/Y'),
            'fecha_corte' => $empleado->vacaciones_fecha_corte?->format('Y-m-d'),
            'fecha_corte_formato' => $empleado->vacaciones_fecha_corte?->format('d/m/Y'),
            'dias_correspondientes' => $this->diasVacacionesCorrespondientes($empleado->fecha_ingreso),
            'saldo_excel' => round($baseExcel, 2),
            'dias_base_excel' => round($baseExcel, 2),
            'proporcional_generado' => round($proporcional, 2),
            'saldo_anio_anterior' => round($simulacion['saldo_anterior'], 2),
            'saldo_anio_actual' => round($simulacion['saldo_actual'], 2),
            'saldo_anterior_vencido' => round($simulacion['vencido'], 2),
            'fecha_vencimiento' => Carbon::parse($simulacion['fecha_vencimiento'])->format('d/m/Y'),
            'dias_usados' => round($simulacion['consumido_anterior'] + $simulacion['consumido_actual'], 2),
            'dias_tomados' => $consumo['tomados'],
            'dias_apartados' => $consumo['apartados'],
            'dias_pendientes_formato' => 0,
            'dias_disponibles' => $simulacion['disponible'],
            'dias_restantes' => $simulacion['disponible'],
            'dias_ganados_hoy' => round($simulacion['saldo_anterior'] + $simulacion['saldo_actual'], 2),
            'dias_asignados_total' => round($simulacion['saldo_anterior'] + $simulacion['saldo_actual'], 2),
            'dias_ajuste' => 0,
        ];
    }

    public function validarFechasSuficientes(Empleado $empleado, TipoPermiso $tipoPermiso, array $fechas): bool
    {
        if (! $tipoPermiso->requiere_saldo && ! $tipoPermiso->descuenta_vacaciones) {
            return true;
        }

        return $this->simular($empleado, $fechas)['deficit'] <= 0;
    }

    public function validarSaldoSuficiente(Empleado $empleado, TipoPermiso $tipoPermiso, float $diasSolicitados): bool
    {
        if (! $tipoPermiso->requiere_saldo && ! $tipoPermiso->descuenta_vacaciones) {
            return true;
        }

        return round($diasSolicitados, 2) <= $this->resumen($empleado)['dias_disponibles'];
    }
}
