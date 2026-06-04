<?php

namespace App\Http\Controllers;

use App\Models\PerfilPuesto;
use Illuminate\Http\Request;

class PerfilPuestoApiController extends Controller
{
    public function buscar(Request $request)
    {
        $q = trim((string) $request->get('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        return PerfilPuesto::query()
            ->where('activo', 1)
            ->where(function ($query) use ($q) {
                $query->where('nombre_puesto', 'like', "%{$q}%")
                    ->orWhere('area_departamento', 'like', "%{$q}%")
                    ->orWhere('puesto_reporta', 'like', "%{$q}%");
            })
            ->orderBy('nombre_puesto')
            ->limit(15)
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
