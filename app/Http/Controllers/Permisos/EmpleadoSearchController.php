<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Services\Permisos\PermisoSaldoService;
use Illuminate\Http\Request;

class EmpleadoSearchController extends Controller
{
    public function __invoke(Request $request, PermisoSaldoService $saldoService)
    {
        $q = trim((string) $request->query('q', ''));
        $areaId = $request->query('area_id');

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $empleados = Empleado::with(['area', 'lider'])
            ->where('activo', true)
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%");
            })
            ->orderBy('nombre')
            ->limit(15)
            ->get()
            ->map(function ($empleado) use ($saldoService) {
                return [
                    'id' => $empleado->id,
                    'numero_empleado' => $empleado->numero_empleado,
                    'nombre' => $empleado->nombre,
                    'correo' => $empleado->correo,
                    'puesto' => $empleado->puesto,
                    'area' => $empleado->area?->nombre,
                    'lider' => $empleado->lider?->nombre,
                    'correo_lider' => $empleado->lider?->correo,
                    'saldo' => $saldoService->resumen($empleado),
                ];
            });

        return response()->json($empleados);
    }
}
