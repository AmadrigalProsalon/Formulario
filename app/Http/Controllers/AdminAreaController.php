<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAreaController extends Controller
{
    public function index()
    {
        $areas = Area::withCount('empleados')->orderBy('nombre')->paginate(30);
        return view('admin.permisos.areas', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:areas,nombre'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['nullable', 'boolean'],
        ]);

        Area::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('success', 'Área creada correctamente.');
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('areas', 'nombre')->ignore($area->id)],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $area->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area)
    {
        if ($area->empleados()->exists()) {
            return back()->with('error', 'No puedes eliminar un área con empleados asignados.');
        }

        $area->delete();
        return back()->with('success', 'Área eliminada correctamente.');
    }
}
