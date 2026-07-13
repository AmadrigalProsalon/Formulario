<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Services\Permisos\CalendarioLaboralService;
use App\Services\Permisos\PermisoSaldoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmpleadoSearchController extends Controller
{
    public function __invoke(
        Request $request,
        PermisoSaldoService $saldoService,
        CalendarioLaboralService $calendarioService
    ) {
        $q = trim((string) $request->query('q', ''));
        $areaId = $request->query('area_id');

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $normalizado = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $q));

        $empleados = Empleado::with(['area', 'lider'])
            ->where('activo', true)
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->where(function ($query) use ($q, $normalizado) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%")
                    ->orWhere('puesto', 'like', "%{$q}%")
                    ->orWhere('curp', 'like', "%{$normalizado}%")
                    ->orWhere('rfc', 'like', "%{$normalizado}%");
            })
            ->orderByRaw("CASE WHEN curp = ? OR rfc = ? THEN 0 ELSE 1 END", [$normalizado, $normalizado])
            ->orderBy('nombre')
            ->limit(15)
            ->get()
            ->map(function ($empleado) use ($saldoService, $calendarioService) {
                return [
                    'id' => $empleado->id,
                    'numero_empleado' => $empleado->numero_empleado,
                    'curp' => $empleado->curp,
                    'rfc' => $empleado->rfc,
                    'nombre' => $empleado->nombre,
                    'correo' => $empleado->correo,
                    'puesto' => $empleado->puesto,
                    'fecha_ingreso' => $empleado->fecha_ingreso?->format('Y-m-d'),
                    'fecha_ingreso_formato' => $empleado->fecha_ingreso?->format('d/m/Y'),
                    'area' => $empleado->area?->nombre,
                    'lider' => $empleado->lider?->nombre,
                    'correo_lider' => $empleado->lider?->correo,
                    'saldo' => $saldoService->resumen($empleado),
                    'calendario_laboral' => [
                        'dias' => $calendarioService->diasLaboralesEmpleado($empleado),
                        'descripcion' => $calendarioService->descripcionHorario($empleado),
                    ],
                ];
            });

        return response()->json($empleados);
    }
}
