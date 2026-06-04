<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerfilPuesto;
use App\Services\PerfilPuestoDocxParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilPuestoController extends Controller
{
    public function index(Request $request)
    {
        $query = PerfilPuesto::query()->withCount('responsabilidades');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($subquery) use ($q) {
                $subquery->where('nombre_puesto', 'like', "%{$q}%")
                    ->orWhere('area_departamento', 'like', "%{$q}%")
                    ->orWhere('puesto_reporta', 'like', "%{$q}%");
            });
        }

        if ($request->filled('area')) {
            $query->where('area_departamento', $request->area);
        }

        if ($request->filled('activo')) {
            $query->where('activo', (bool) $request->activo);
        }

        $perfiles = $query->orderBy('nombre_puesto')->paginate(20)->withQueryString();

        $areas = PerfilPuesto::whereNotNull('area_departamento')
            ->where('area_departamento', '!=', '')
            ->distinct()
            ->orderBy('area_departamento')
            ->pluck('area_departamento');

        return view('admin.perfiles_puesto.index', compact('perfiles', 'areas'));
    }

    public function importar(Request $request, PerfilPuestoDocxParser $parser)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:docx', 'max:10240'],
        ]);

        $file = $request->file('archivo');
        $path = $file->store('perfiles_puesto/originales');
        $data = $parser->parse(Storage::path($path), $file->getClientOriginalName());

        $perfil = PerfilPuesto::create([
            'nombre_puesto' => $data['nombre_puesto'],
            'codigo' => $data['codigo'],
            'version' => $data['version'],
            'fecha_elaboracion' => $data['fecha_elaboracion'],
            'organizacion' => $data['organizacion'],
            'area_departamento' => $data['area_departamento'],
            'puesto_reporta' => $data['puesto_reporta'],
            'descripcion_puesto' => $data['descripcion_puesto'],
            'objetivo_puesto' => $data['objetivo_puesto'],
            'requerimientos_minimos' => $data['requerimientos_minimos'],
            'cualidades' => $data['cualidades'],
            'habilidades' => $data['habilidades'],
            'responsabilidades_text' => $data['responsabilidades_text'],
            'archivo_original_path' => $path,
            'activo' => true,
            'raw_text' => $data['raw_text'],
        ]);

        foreach ($data['responsabilidades'] ?? [] as $item) {
            $perfil->responsabilidades()->create([
                'titulo' => $item['titulo'],
                'descripcion' => $item['descripcion'] ?? null,
                'orden' => $item['orden'] ?? 1,
            ]);
        }

        return redirect()
            ->route('admin.perfiles-puesto.show', $perfil)
            ->with('success', 'Perfil importado desde Word correctamente. Revisa la información detectada antes de usarlo en requisiciones.');
    }

    public function show(PerfilPuesto $perfil)
    {
        $perfil->load('responsabilidades');

        return view('admin.perfiles_puesto.show', compact('perfil'));
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
            'responsabilidades_text' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['activo'] = $request->boolean('activo');

        $perfil->update($validated);

        return redirect()
            ->route('admin.perfiles-puesto.show', $perfil)
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function activar(PerfilPuesto $perfil)
    {
        $perfil->update(['activo' => true]);
        return back()->with('success', 'Perfil activado.');
    }

    public function desactivar(PerfilPuesto $perfil)
    {
        $perfil->update(['activo' => false]);
        return back()->with('success', 'Perfil desactivado.');
    }

    public function descargarOriginal(PerfilPuesto $perfil)
    {
        abort_unless($perfil->archivo_original_path && Storage::exists($perfil->archivo_original_path), 404);

        return Storage::download($perfil->archivo_original_path);
    }
}
