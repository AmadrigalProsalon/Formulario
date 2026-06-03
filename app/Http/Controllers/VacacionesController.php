<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Services\VacacionesService;
use Illuminate\Http\Request;
use Throwable;

class VacacionesController extends Controller
{
    public function create()
    {
        return view('vacaciones.solicitud');
    }

    public function consultarEmpleado(Request $request, VacacionesService $service)
    {
        $validated = $request->validate([
            'identificador' => ['required', 'string', 'max:255'],
        ]);

        $identificador = trim($validated['identificador']);

        $empleado = Empleado::where('activo', true)
            ->where(function ($query) use ($identificador) {
                $query->where('numero_empleado', $identificador)
                    ->orWhere('correo', $identificador);
            })
            ->first();

        if (! $empleado) {
            return response()->json([
                'ok' => false,
                'message' => 'No se encontró un empleado activo con ese número o correo.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'empleado' => [
                'id' => $empleado->id,
                'numero_empleado' => $empleado->numero_empleado,
                'nombre' => $empleado->nombre,
                'correo' => $empleado->correo,
                'departamento' => $empleado->departamento,
                'puesto' => $empleado->puesto,
                'fecha_ingreso' => optional($empleado->fecha_ingreso)->format('Y-m-d'),
            ],
            'saldo' => $service->resumen($empleado),
        ]);
    }

    public function calcularDias(Request $request, VacacionesService $service)
    {
        $validated = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        return response()->json([
            'ok' => true,
            'dias' => $service->calcularDiasLaborables($validated['fecha_inicio'], $validated['fecha_fin']),
        ]);
    }

    public function store(Request $request, VacacionesService $service)
    {
        $validated = $request->validate([
            'identificador' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'comentarios_empleado' => ['nullable', 'string', 'max:5000'],
        ], [
            'identificador.required' => 'Ingresa tu número de empleado o correo.',
            'fecha_inicio.required' => 'Selecciona la fecha de inicio.',
            'fecha_fin.required' => 'Selecciona la fecha de fin.',
            'fecha_fin.after_or_equal' => 'La fecha final no puede ser menor que la fecha inicial.',
        ]);

        $empleado = Empleado::where('activo', true)
            ->where(function ($query) use ($validated) {
                $query->where('numero_empleado', $validated['identificador'])
                    ->orWhere('correo', $validated['identificador']);
            })
            ->first();

        if (! $empleado) {
            return back()
                ->with('error', 'No se encontró un empleado activo con ese número o correo.')
                ->withInput();
        }

        try {
            $solicitud = $service->crearSolicitud($empleado, $validated);

            return redirect()
                ->route('vacaciones.create')
                ->with('success', 'Tu solicitud de vacaciones fue registrada correctamente. Folio: #' . $solicitud->id);
        } catch (Throwable $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }
}
