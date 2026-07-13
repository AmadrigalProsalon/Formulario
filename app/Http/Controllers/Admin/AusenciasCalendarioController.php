<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AusenciasCalendarioController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.ausencias.calendario', $this->buildCalendarData($request));
    }

    private function buildCalendarData(Request $request): array
    {
        $anio = (int) $request->input('anio', now()->year);
        $mes = (int) $request->input('mes', now()->month);

        if ($mes < 1 || $mes > 12) {
            $mes = now()->month;
        }

        if ($anio < 2000 || $anio > 2100) {
            $anio = now()->year;
        }

        $fechaBase = Carbon::create($anio, $mes, 1)->locale('es')->startOfMonth();

        $inicioCalendario = $fechaBase->copy()
            ->startOfMonth()
            ->startOfWeek(Carbon::MONDAY);

        $finCalendario = $fechaBase->copy()
            ->endOfMonth()
            ->endOfWeek(Carbon::SUNDAY);

        $diasCalendario = collect(CarbonPeriod::create($inicioCalendario, $finCalendario));

        $areas = Schema::hasTable('areas')
            ? DB::table('areas')->orderBy('nombre')->get()
            : collect();

        $tipos = Schema::hasTable('tipos_permisos')
            ? DB::table('tipos_permisos')->orderBy('nombre')->get()
            : collect();

        $solicitudes = $this->getSolicitudes($request, $inicioCalendario, $finCalendario);

        $solicitudesPorDia = $this->agruparSolicitudesPorDia(
            $solicitudes,
            $inicioCalendario,
            $finCalendario
        );

        $nombresMeses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

        $estatusOptions = [
            'formato_enviado' => 'Formato enviado',
            'formato_pendiente' => 'Formato pendiente',
            'formato_recibido' => 'Formato recibido',
            'con_observaciones' => 'Con observaciones',
            'cancelado' => 'Cancelado',
        ];

        return compact(
            'anio',
            'mes',
            'fechaBase',
            'inicioCalendario',
            'finCalendario',
            'diasCalendario',
            'solicitudesPorDia',
            'nombresMeses',
            'diasSemana',
            'areas',
            'tipos',
            'estatusOptions'
        );
    }

    private function getSolicitudes(Request $request, Carbon $inicioCalendario, Carbon $finCalendario)
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            return collect();
        }

        if (
            ! Schema::hasColumn('permisos_solicitudes', 'fecha_inicio') ||
            ! Schema::hasColumn('permisos_solicitudes', 'fecha_fin')
        ) {
            return collect();
        }

        $query = DB::table('permisos_solicitudes as s');

        $select = [
            's.id',
            's.empleado_id',
            's.fecha_inicio',
            's.fecha_fin',
            's.dias_solicitados',
            's.estatus',
        ];

        if (Schema::hasColumn('permisos_solicitudes', 'motivo')) {
            $select[] = 's.motivo';
        } else {
            $select[] = DB::raw('NULL as motivo');
        }

        if (Schema::hasColumn('permisos_solicitudes', 'empleado_id') && Schema::hasTable('empleados')) {
            $query->leftJoin('empleados as e', 'e.id', '=', 's.empleado_id');
            $select[] = 'e.nombre as empleado_nombre';
            $select[] = 'e.puesto as empleado_puesto';
            $select[] = 'e.correo as empleado_correo';
        } else {
            $select[] = DB::raw('NULL as empleado_nombre');
            $select[] = DB::raw('NULL as empleado_puesto');
            $select[] = DB::raw('NULL as empleado_correo');
        }

        if (Schema::hasColumn('permisos_solicitudes', 'area_id') && Schema::hasTable('areas')) {
            $query->leftJoin('areas as a', 'a.id', '=', 's.area_id');
            $select[] = 'a.nombre as area_nombre';
        } else {
            $select[] = DB::raw('NULL as area_nombre');
        }

        if (Schema::hasColumn('permisos_solicitudes', 'tipo_permiso_id') && Schema::hasTable('tipos_permisos')) {
            $query->leftJoin('tipos_permisos as tp', 'tp.id', '=', 's.tipo_permiso_id');
            $select[] = 'tp.nombre as tipo_permiso_nombre';
        } else {
            $select[] = DB::raw('NULL as tipo_permiso_nombre');
        }

        $query->select($select)
            ->whereDate('s.fecha_inicio', '<=', $finCalendario->toDateString())
            ->whereDate('s.fecha_fin', '>=', $inicioCalendario->toDateString());

        if (
            $request->filled('area_id') &&
            Schema::hasColumn('permisos_solicitudes', 'area_id')
        ) {
            $query->where('s.area_id', $request->input('area_id'));
        }

        if (
            $request->filled('tipo_permiso_id') &&
            Schema::hasColumn('permisos_solicitudes', 'tipo_permiso_id')
        ) {
            $query->where('s.tipo_permiso_id', $request->input('tipo_permiso_id'));
        }

        if (
            $request->filled('estatus') &&
            Schema::hasColumn('permisos_solicitudes', 'estatus')
        ) {
            $query->where('s.estatus', $request->input('estatus'));
        }

        return $query
            ->orderBy('s.fecha_inicio')
            ->orderBy('empleado_nombre')
            ->get();
    }

    private function agruparSolicitudesPorDia(
        $solicitudes,
        Carbon $inicioCalendario,
        Carbon $finCalendario
    ) {
        $solicitudesPorDia = collect();

        if ($solicitudes->isEmpty()) {
            return $solicitudesPorDia;
        }

        $diasEspecificos = collect();

        if (Schema::hasTable('permiso_solicitud_dias')) {
            $diasEspecificos = DB::table('permiso_solicitud_dias')
                ->whereIn('permiso_solicitud_id', $solicitudes->pluck('id'))
                ->orderBy('fecha')
                ->get()
                ->groupBy('permiso_solicitud_id');
        }

        foreach ($solicitudes as $solicitud) {
            $diasSolicitud = $diasEspecificos->get($solicitud->id, collect());

            if ($diasSolicitud->isNotEmpty()) {
                $solicitud->fechas_seleccionadas = $diasSolicitud
                    ->pluck('fecha')
                    ->map(fn ($fecha) => Carbon::parse($fecha)->format('Y-m-d'))
                    ->values()
                    ->all();

                foreach ($diasSolicitud as $diaSolicitud) {
                    $fechaDia = Carbon::parse($diaSolicitud->fecha)->startOfDay();

                    if (! $fechaDia->betweenIncluded($inicioCalendario, $finCalendario)) {
                        continue;
                    }

                    $key = $fechaDia->format('Y-m-d');

                    if (! $solicitudesPorDia->has($key)) {
                        $solicitudesPorDia[$key] = collect();
                    }

                    $solicitudesPorDia[$key]->push($solicitud);
                }

                continue;
            }

            $solicitud->fechas_seleccionadas = [];

            $desde = Carbon::parse($solicitud->fecha_inicio)->startOfDay();
            $hasta = Carbon::parse($solicitud->fecha_fin)->startOfDay();

            if ($desde->lt($inicioCalendario)) {
                $desde = $inicioCalendario->copy();
            }

            if ($hasta->gt($finCalendario)) {
                $hasta = $finCalendario->copy();
            }

            foreach (CarbonPeriod::create($desde, $hasta) as $dia) {
                $key = $dia->format('Y-m-d');

                if (! $solicitudesPorDia->has($key)) {
                    $solicitudesPorDia[$key] = collect();
                }

                $solicitudesPorDia[$key]->push($solicitud);
            }
        }

        return $solicitudesPorDia;
    }
}
