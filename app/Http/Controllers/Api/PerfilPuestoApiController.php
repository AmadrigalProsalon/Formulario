<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerfilPuesto;
use Illuminate\Http\Request;

class PerfilPuestoApiController extends Controller
{
    public function buscar(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $perfiles = PerfilPuesto::query()
            ->activos()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subquery) use ($q) {
                    $subquery->where('nombre_puesto', 'like', "%{$q}%")
                        ->orWhere('area_departamento', 'like', "%{$q}%")
                        ->orWhere('puesto_reporta', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre_puesto')
            ->limit(20)
            ->get(['id', 'nombre_puesto', 'area_departamento', 'puesto_reporta']);

        return response()->json($perfiles);
    }

    public function show(PerfilPuesto $perfil)
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
            'responsabilidades_text' => $perfil->responsabilidades_text,
            'nivel_ingles' => $this->inferirNivelIngles($perfil->requerimientos_minimos),
            'anios_experiencia' => $this->inferirAniosExperiencia($perfil->requerimientos_minimos),
        ]);
    }

    private function inferirNivelIngles(?string $text): ?string
    {
        $t = mb_strtolower($text ?? '');
        if (str_contains($t, 'avanzado')) return 'Avanzado';
        if (str_contains($t, 'intermedio')) return 'Intermedio';
        if (str_contains($t, 'básico') || str_contains($t, 'basico')) return 'Básico';
        return null;
    }

    private function inferirAniosExperiencia(?string $text): ?string
    {
        $t = mb_strtolower($text ?? '');
        if (preg_match('/mínimo\s*2|minimo\s*2|2\s*años|2\s+años/u', $t)) return '1 a 2 años';
        if (preg_match('/3\s*a\s*5|3\s+años|5\s+años/u', $t)) return '3 a 5 años';
        if (preg_match('/1\s*año|1\s+ano|1\s+años/u', $t)) return '0 a 1 año';
        return null;
    }
}
