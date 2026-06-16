<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $normalizado = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $q));

            $query->where(function ($sub) use ($q, $normalizado) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%")
                    ->orWhere('curp', 'like', "%{$normalizado}%")
                    ->orWhere('rfc', 'like', "%{$normalizado}%")
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
            'curp' => ['nullable', 'string', 'max:18'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'vacaciones_ajuste' => ['nullable', 'numeric'],
            'es_lider' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['curp'] = $this->normalizarClave($validated['curp'] ?? null);
        $validated['rfc'] = $this->normalizarClave($validated['rfc'] ?? null);
        $validated['vacaciones_ajuste'] = $validated['vacaciones_ajuste'] ?? 0;
        $validated['es_lider'] = $request->boolean('es_lider');
        $validated['activo'] = $request->boolean('activo');

        $empleado->update($validated);

        return back()->with('success', 'Empleado actualizado correctamente.');
    }

    private function normalizarClave(?string $valor): ?string
    {
        $valor = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $valor));

        return $valor !== '' ? $valor : null;
    }
}
