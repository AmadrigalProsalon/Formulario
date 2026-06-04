<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminEmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::with(['area', 'lider'])->orderBy('nombre');

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
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

        $empleados = $query->paginate(30)->withQueryString();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();
        $lideres = Empleado::where('activo', true)->where('es_lider', true)->orderBy('nombre')->get();

        return view('admin.permisos.empleados', compact('empleados', 'areas', 'lideres'));
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        Empleado::create($validated);

        return back()->with('success', 'Empleado creado correctamente.');
    }

    public function update(Request $request, Empleado $empleado)
    {
        $validated = $this->validar($request, $empleado->id);
        $empleado->update($validated);

        return back()->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Empleado $empleado)
    {
        if ($empleado->permisos()->exists()) {
            return back()->with('error', 'No puedes eliminar un empleado con solicitudes registradas. Puedes desactivarlo.');
        }

        $empleado->delete();
        return back()->with('success', 'Empleado eliminado correctamente.');
    }

    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'lider_id' => ['nullable', 'exists:empleados,id'],
            'numero_empleado' => ['nullable', 'string', 'max:100', Rule::unique('empleados', 'numero_empleado')->ignore($ignoreId)],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'max:255', Rule::unique('empleados', 'correo')->ignore($ignoreId)],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'vacaciones_ajuste' => ['nullable', 'numeric'],
            'vacaciones_usados' => ['nullable', 'numeric'],
            'vacaciones_pendientes' => ['nullable', 'numeric'],
            'es_lider' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['es_lider'] = $request->boolean('es_lider');
        $validated['activo'] = $request->boolean('activo');
        $validated['vacaciones_ajuste'] = $validated['vacaciones_ajuste'] ?? 0;
        $validated['vacaciones_usados'] = $validated['vacaciones_usados'] ?? 0;
        $validated['vacaciones_pendientes'] = $validated['vacaciones_pendientes'] ?? 0;

        return $validated;
    }
}
