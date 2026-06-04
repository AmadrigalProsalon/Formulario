<?php

namespace App\Http\Controllers;

use App\Models\TipoPermiso;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminTipoPermisoController extends Controller
{
    public function index()
    {
        $tipos = TipoPermiso::orderBy('nombre')->paginate(30);
        return view('admin.permisos.tipos', compact('tipos'));
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['nombre']);
        TipoPermiso::create($validated);

        return back()->with('success', 'Tipo de permiso creado correctamente.');
    }

    public function update(Request $request, TipoPermiso $tipoPermiso)
    {
        $validated = $this->validar($request, $tipoPermiso->id);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['nombre']);
        $tipoPermiso->update($validated);

        return back()->with('success', 'Tipo de permiso actualizado correctamente.');
    }

    public function destroy(TipoPermiso $tipoPermiso)
    {
        if ($tipoPermiso->solicitudes()->exists()) {
            return back()->with('error', 'No puedes eliminar un tipo con solicitudes. Puedes desactivarlo.');
        }

        $tipoPermiso->delete();
        return back()->with('success', 'Tipo eliminado correctamente.');
    }

    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tipos_permisos', 'slug')->ignore($ignoreId)],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'descuenta_vacaciones' => ['nullable', 'boolean'],
            'requiere_saldo' => ['nullable', 'boolean'],
            'requiere_firma_colaborador' => ['nullable', 'boolean'],
            'requiere_firma_lider' => ['nullable', 'boolean'],
            'requiere_recepcion_rh' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        foreach (['descuenta_vacaciones', 'requiere_saldo', 'requiere_firma_colaborador', 'requiere_firma_lider', 'requiere_recepcion_rh', 'activo'] as $campo) {
            $validated[$campo] = $request->boolean($campo);
        }

        return $validated;
    }
}
