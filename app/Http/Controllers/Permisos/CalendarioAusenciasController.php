<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalendarioAusenciasController extends Controller
{
    public function index(Request $request)
    {
        $fechaBase = Carbon::create(
            (int) $request->query('anio', now()->year),
            (int) $request->query('mes', now()->month),
            1
        );

        $inicioMes = $fechaBase->copy()->startOfMonth();
        $finMes = $fechaBase->copy()->endOfMonth();
        $inicioCalendario = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $finCalendario = $finMes->copy()->endOfWeek(Carbon::SUNDAY);

        $dias = [];
        $cursor = $inicioCalendario->copy();

        while ($cursor->lte($finCalendario)) {
            $key = $cursor->format('Y-m-d');
            $dias[$key] = [
                'fecha' => $cursor->copy(),
                'en_mes' => $cursor->month === $fechaBase->month,
                'eventos' => [],
            ];
            $cursor->addDay();
        }

        $solicitudes = collect();
        $areas = collect();
        $tiposPermiso = collect();

        if (Schema::hasTable('areas')) {
            $areas = DB::table('areas')->select('id', 'nombre')->orderBy('nombre')->get();
        }

        if (Schema::hasTable('tipos_permisos')) {
            $tiposPermiso = DB::table('tipos_permisos')->select('id', 'nombre')->orderBy('nombre')->get();
        }

        if (Schema::hasTable('permisos_solicitudes')) {
            $query = DB::table('permisos_solicitudes as ps')
                ->leftJoin('empleados as e', 'e.id', '=', 'ps.empleado_id')
                ->leftJoin('areas as a', 'a.id', '=', 'ps.area_id')
                ->leftJoin('tipos_permisos as tp', 'tp.id', '=', 'ps.tipo_permiso_id')
                ->select(
                    'ps.id',
                    'ps.fecha_inicio',
                    'ps.fecha_fin',
                    'ps.dias_solicitados',
                    'ps.estatus',
                    'ps.formato_recibido',
                    'ps.observaciones_rh',
                    'ps.created_at',
                    'e.nombre as empleado_nombre',
                    'e.puesto as empleado_puesto',
                    'a.nombre as area_nombre',
                    'tp.nombre as tipo_permiso_nombre'
                )
                ->whereDate('ps.fecha_inicio', '<=', $finCalendario->format('Y-m-d'))
                ->whereDate('ps.fecha_fin', '>=', $inicioCalendario->format('Y-m-d'));

            if ($request->filled('area_id')) {
                $query->where('ps.area_id', $request->query('area_id'));
            }

            if ($request->filled('tipo_permiso_id')) {
                $query->where('ps.tipo_permiso_id', $request->query('tipo_permiso_id'));
            }

            if ($request->filled('estatus')) {
                $query->where('ps.estatus', $request->query('estatus'));
            }

            $solicitudes = $query
                ->orderBy('ps.fecha_inicio')
                ->orderBy('e.nombre')
                ->get()
                ->map(function ($solicitud) {
                    $solicitud->fecha_inicio_carbon = Carbon::parse($solicitud->fecha_inicio)->startOfDay();
                    $solicitud->fecha_fin_carbon = Carbon::parse($solicitud->fecha_fin)->startOfDay();
                    $solicitud->clase_estado = $this->claseEstado($solicitud->estatus, (bool) $solicitud->formato_recibido);
                    $solicitud->etiqueta_estado = $this->etiquetaEstado($solicitud->estatus, (bool) $solicitud->formato_recibido);
                    return $solicitud;
                });

            $diasSeleccionados = collect();

            if (Schema::hasTable('permiso_solicitud_dias') && $solicitudes->isNotEmpty()) {
                $diasSeleccionados = DB::table('permiso_solicitud_dias')
                    ->whereIn('permiso_solicitud_id', $solicitudes->pluck('id'))
                    ->whereBetween('fecha', [$inicioCalendario->format('Y-m-d'), $finCalendario->format('Y-m-d')])
                    ->orderBy('fecha')
                    ->get()
                    ->groupBy('permiso_solicitud_id');
            }

            foreach ($solicitudes as $solicitud) {
                $diasEspecificos = $diasSeleccionados->get($solicitud->id, collect());

                if ($diasEspecificos->isNotEmpty()) {
                    foreach ($diasEspecificos as $diaSolicitud) {
                        $key = Carbon::parse($diaSolicitud->fecha)->format('Y-m-d');
                        if (isset($dias[$key])) {
                            $dias[$key]['eventos'][] = $solicitud;
                        }
                    }
                    continue;
                }

                foreach ($dias as &$dia) {
                    if ($dia['fecha']->betweenIncluded($solicitud->fecha_inicio_carbon, $solicitud->fecha_fin_carbon)) {
                        $dia['eventos'][] = $solicitud;
                    }
                }
                unset($dia);
            }
        }

        $estatusOpciones = [
            'formato_enviado' => 'Formato enviado',
            'formato_pendiente' => 'Formato pendiente',
            'formato_recibido' => 'Formato recibido',
            'con_observaciones' => 'Con observaciones',
            'cancelado' => 'Cancelado',
        ];

        return view('admin.permisos.calendario', [
            'fechaBase' => $fechaBase,
            'inicioMes' => $inicioMes,
            'finMes' => $finMes,
            'dias' => $dias,
            'areas' => $areas,
            'tiposPermiso' => $tiposPermiso,
            'estatusOpciones' => $estatusOpciones,
            'totalSolicitudes' => $solicitudes->count(),
            'prev' => $fechaBase->copy()->subMonth(),
            'next' => $fechaBase->copy()->addMonth(),
        ]);
    }

    private function claseEstado(?string $estatus, bool $formatoRecibido): string
    {
        if ($formatoRecibido || $estatus === 'formato_recibido') {
            return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        }

        return match ($estatus) {
            'cancelado' => 'bg-rose-100 text-rose-800 border-rose-200',
            'con_observaciones' => 'bg-orange-100 text-orange-800 border-orange-200',
            'formato_pendiente' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-blue-100 text-blue-800 border-blue-200',
        };
    }

    private function etiquetaEstado(?string $estatus, bool $formatoRecibido): string
    {
        if ($formatoRecibido || $estatus === 'formato_recibido') {
            return 'Recibido';
        }

        return match ($estatus) {
            'cancelado' => 'Cancelado',
            'con_observaciones' => 'Observaciones',
            'formato_pendiente' => 'Pendiente',
            'formato_enviado' => 'Enviado',
            default => 'En proceso',
        };
    }
}
