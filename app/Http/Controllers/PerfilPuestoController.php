<?php

namespace App\Http\Controllers;

use App\Models\PerfilPuesto;
use App\Services\PerfilPuestoDocxParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilPuestoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $area = trim((string) $request->get('area'));

        $perfiles = PerfilPuesto::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre_puesto', 'like', "%{$q}%")
                        ->orWhere('area_departamento', 'like', "%{$q}%")
                        ->orWhere('puesto_reporta', 'like', "%{$q}%");
                });
            })
            ->when($area, fn ($query) => $query->where('area_departamento', $area))
            ->orderBy('nombre_puesto')
            ->paginate(20)
            ->withQueryString();

        $areas = PerfilPuesto::query()
            ->whereNotNull('area_departamento')
            ->where('area_departamento', '!=', '')
            ->select('area_departamento')
            ->distinct()
            ->orderBy('area_departamento')
            ->pluck('area_departamento');

        return view('admin.perfiles-puesto.index', compact('perfiles', 'areas', 'q', 'area'));
    }

    public function store(Request $request, PerfilPuestoDocxParser $parser)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:docx', 'max:20480'],
        ]);

        $archivo = $request->file('archivo');
        $path = $archivo->store('perfiles-puesto/originales', 'public');
        $absolutePath = Storage::disk('public')->path($path);

        $datos = $parser->parse($absolutePath);

        $perfil = PerfilPuesto::updateOrCreate(
            ['nombre_puesto' => $datos['nombre_puesto']],
            [
                'codigo' => $datos['codigo'] ?? null,
                'version' => $datos['version'] ?? null,
                'fecha_elaboracion' => $datos['fecha_elaboracion'] ?? null,
                'organizacion' => $datos['organizacion'] ?? null,
                'area_departamento' => $datos['area_departamento'] ?? null,
                'puesto_reporta' => $datos['puesto_reporta'] ?? null,
                'descripcion_puesto' => $datos['descripcion_puesto'] ?? null,
                'objetivo_puesto' => $datos['objetivo_puesto'] ?? null,
                'requerimientos_minimos' => $datos['requerimientos_minimos'] ?? null,
                'cualidades' => $datos['cualidades'] ?? null,
                'habilidades' => $datos['habilidades'] ?? null,
                'responsabilidades' => $datos['responsabilidades'] ?? null,
                'escolaridad_detectada' => $datos['escolaridad_detectada'] ?? null,
                'experiencia_detectada' => $datos['experiencia_detectada'] ?? null,
                'ingles_detectado' => $datos['ingles_detectado'] ?? null,
                'software_detectado' => $datos['software_detectado'] ?? null,
                'archivo_original_path' => $path,
                'texto_original' => $datos['texto_original'] ?? null,
                'activo' => true,
            ]
        );

        return redirect()
            ->route('admin.perfiles-puesto.index')
            ->with('success', 'Perfil importado correctamente: ' . $perfil->nombre_puesto);
    }

    public function update(Request $request, PerfilPuesto $perfil)
    {
        $validated = $request->validate([
            'nombre_puesto' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:255'],
            'fecha_elaboracion' => ['nullable', 'string', 'max:255'],
            'organizacion' => ['nullable', 'string', 'max:255'],
            'area_departamento' => ['nullable', 'string', 'max:255'],
            'puesto_reporta' => ['nullable', 'string', 'max:255'],
            'descripcion_puesto' => ['nullable', 'string'],
            'objetivo_puesto' => ['nullable', 'string'],
            'requerimientos_minimos' => ['nullable', 'string'],
            'cualidades' => ['nullable', 'string'],
            'habilidades' => ['nullable', 'string'],
            'responsabilidades' => ['nullable', 'string'],
            'escolaridad_detectada' => ['nullable', 'string'],
            'experiencia_detectada' => ['nullable', 'string'],
            'ingles_detectado' => ['nullable', 'string', 'max:255'],
            'software_detectado' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['activo'] = $request->boolean('activo');
        $perfil->update($validated);

        return redirect()
            ->route('admin.perfiles-puesto.index')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(PerfilPuesto $perfil)
    {
        $perfil->delete();

        return redirect()
            ->route('admin.perfiles-puesto.index')
            ->with('success', 'Perfil eliminado correctamente.');
    }
}
