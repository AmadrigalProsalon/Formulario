<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Empleado;
use Illuminate\Http\Request;

class PermisosEmpleadosController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::with(['area', 'lider'])->orderBy('nombre');

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%")
                    ->orWhere('puesto', 'like', "%{$q}%");
            });
        }

        return view('admin.permisos-catalogos.empleados.index', [
            'empleados' => $query->paginate(25)->withQueryString(),
            'areas' => Area::orderBy('nombre')->get(),
            'lideres' => Empleado::where('es_lider', true)->orderBy('nombre')->get(),
            'filters' => $request->only(['area_id', 'activo', 'q']),
        ]);
    }

    public function update(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'lider_id' => ['nullable', 'exists:empleados,id'],
            'numero_empleado' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'es_lider' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['es_lider'] = $request->boolean('es_lider');
        $validated['activo'] = $request->boolean('activo');

        $empleado->update($validated);

        return back()->with('success', 'Empleado actualizado correctamente.');
    }
}
