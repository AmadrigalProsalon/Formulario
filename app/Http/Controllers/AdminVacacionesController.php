<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\VacacionesAjuste;
use App\Models\VacacionesDiaInhabil;
use App\Models\VacacionesSolicitud;
use App\Services\VacacionesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminVacacionesController extends Controller
{
    public function index(Request $request, VacacionesService $service)
    {
        $query = VacacionesSolicitud::with('empleado', 'aprobador')->latest();

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->whereHas('empleado', function ($subquery) use ($q) {
                $subquery->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%");
            });
        }

        $solicitudes = $query->paginate(20)->withQueryString();
        $filters = $request->only(['q', 'estatus']);

        return view('admin.vacaciones.index', compact('solicitudes', 'filters'));
    }

    public function aprobar(Request $request, VacacionesSolicitud $solicitud, VacacionesService $service)
    {
        if ($solicitud->estatus !== 'pendiente') {
            return back()->with('error', 'Solo se pueden aprobar solicitudes pendientes.');
        }

        $solicitud->update([
            'estatus' => 'aprobada',
            'comentarios_admin' => $request->comentarios_admin,
            'aprobado_por' => auth()->id(),
            'aprobado_at' => now(),
            'rechazado_at' => null,
        ]);

        return back()->with('success', 'Solicitud aprobada correctamente.');
    }

    public function rechazar(Request $request, VacacionesSolicitud $solicitud)
    {
        if ($solicitud->estatus !== 'pendiente') {
            return back()->with('error', 'Solo se pueden rechazar solicitudes pendientes.');
        }

        $request->validate([
            'comentarios_admin' => ['nullable', 'string', 'max:5000'],
        ]);

        $solicitud->update([
            'estatus' => 'rechazada',
            'comentarios_admin' => $request->comentarios_admin,
            'rechazado_at' => now(),
        ]);

        return back()->with('success', 'Solicitud rechazada correctamente.');
    }

    public function empleados(Request $request, VacacionesService $service)
    {
        $query = Empleado::orderBy('nombre');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($subquery) use ($q) {
                $subquery->where('nombre', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('departamento', 'like', "%{$q}%");
            });
        }

        $empleados = $query->paginate(20)->withQueryString();
        $resumenes = [];

        foreach ($empleados as $empleado) {
            $resumenes[$empleado->id] = $service->resumen($empleado);
        }

        return view('admin.vacaciones.empleados', [
            'empleados' => $empleados,
            'resumenes' => $resumenes,
            'filters' => $request->only('q'),
        ]);
    }

    public function storeEmpleado(Request $request)
    {
        $validated = $request->validate([
            'numero_empleado' => ['required', 'string', 'max:100', 'unique:empleados,numero_empleado'],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255', 'unique:empleados,correo'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['activo'] = $request->boolean('activo');

        Empleado::create($validated);

        return back()->with('success', 'Empleado creado correctamente.');
    }

    public function updateEmpleado(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'numero_empleado' => ['required', 'string', 'max:100', Rule::unique('empleados', 'numero_empleado')->ignore($empleado->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255', Rule::unique('empleados', 'correo')->ignore($empleado->id)],
            'departamento' => ['nullable', 'string', 'max:255'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['activo'] = $request->boolean('activo');

        $empleado->update($validated);

        return back()->with('success', 'Empleado actualizado correctamente.');
    }

    public function storeAjuste(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'dias' => ['required', 'numeric'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        VacacionesAjuste::create([
            'empleado_id' => $empleado->id,
            'anio' => $validated['anio'],
            'dias' => $validated['dias'],
            'tipo' => $validated['tipo'] ?: 'ajuste_manual',
            'comentario' => $validated['comentario'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Ajuste agregado correctamente.');
    }

    public function diasInhabiles()
    {
        $dias = VacacionesDiaInhabil::orderBy('fecha', 'desc')->paginate(50);

        return view('admin.vacaciones.inhabiles', compact('dias'));
    }

    public function storeDiaInhabil(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date', 'unique:vacaciones_dias_inhabiles,fecha'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['tipo'] = $validated['tipo'] ?: 'oficial';
        $validated['activo'] = $request->boolean('activo');

        VacacionesDiaInhabil::create($validated);

        return back()->with('success', 'Día inhábil agregado correctamente.');
    }

    public function destroyDiaInhabil(VacacionesDiaInhabil $dia)
    {
        $dia->delete();

        return back()->with('success', 'Día inhábil eliminado correctamente.');
    }
}
