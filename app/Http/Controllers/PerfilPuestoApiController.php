<?php

namespace App\Http\Controllers;

use App\Models\PerfilPuesto;
use Illuminate\Http\Request;

class PerfilPuestoApiController extends Controller
{
    public function areas()
    {
        $areas = PerfilPuesto::query()
            ->where('activo', 1)
            ->whereNotNull('area_departamento')
            ->where('area_departamento', '!=', '')
            ->select('area_departamento')
            ->distinct()
            ->orderBy('area_departamento')
            ->pluck('area_departamento')
            ->values();

        return response()->json($areas);
    }

    public function porDepartamento(Request $request)
    {
        $departamento = trim((string) $request->get('departamento'));

        $perfiles = PerfilPuesto::query()
            ->where('activo', 1)
            ->when($departamento !== '', function ($query) use ($departamento) {
                $query->where(function ($sub) use ($departamento) {
                    $sub->where('area_departamento', $departamento)
                        ->orWhere('area_departamento', 'like', '%' . $departamento . '%');
                });
            })
            ->orderBy('nombre_puesto')
            ->limit(200)
            ->get()
            ->map(fn ($perfil) => [
                'id' => $perfil->id,
                'nombre_puesto' => $perfil->nombre_puesto,
                'area_departamento' => $perfil->area_departamento,
                'puesto_reporta' => $perfil->puesto_reporta,
            ]);

        return response()->json($perfiles);
    }

    public function buscar(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $departamento = trim((string) $request->get('departamento'));

        if (mb_strlen($q) < 2 && $departamento === '') {
            return response()->json([]);
        }

        return PerfilPuesto::query()
            ->where('activo', 1)
            ->when($departamento !== '', function ($query) use ($departamento) {
                $query->where(function ($sub) use ($departamento) {
                    $sub->where('area_departamento', $departamento)
                        ->orWhere('area_departamento', 'like', '%' . $departamento . '%');
                });
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre_puesto', 'like', "%{$q}%")
                        ->orWhere('area_departamento', 'like', "%{$q}%")
                        ->orWhere('puesto_reporta', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre_puesto')
            ->limit(50)
            ->get()
            ->map(fn ($perfil) => [
                'id' => $perfil->id,
                'nombre_puesto' => $perfil->nombre_puesto,
                'area_departamento' => $perfil->area_departamento,
                'puesto_reporta' => $perfil->puesto_reporta,
            ]);
    }

    public function detalle(PerfilPuesto $perfil)
    {
        abort_unless($perfil->activo, 404);

        return response()->json([
            'id' => $perfil->id,
            'nombre_puesto' => $perfil->nombre_puesto,
            'area_departamento' => $perfil->area_departamento,
            'puesto_reporta' => $perfil->puesto_reporta,
            'descripcion_puesto' => $perfil->descripcion_puesto,
            'objetivo_puesto' => $perfil->objetivo_puesto,
            'requerimientos_minimos' => $perfil->requerimientos_minimos,
            'cualidades' => $perfil->cualidades,
            'habilidades' => $perfil->habilidades,
            'responsabilidades' => $perfil->responsabilidades,
            'escolaridad_detectada' => $perfil->escolaridad_detectada,
            'experiencia_detectada' => $perfil->experiencia_detectada,
            'ingles_detectado' => $perfil->ingles_detectado,
            'software_detectado' => $perfil->software_detectado,
        ]);
    }
}
