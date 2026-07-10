<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\DiaInhabil;
use Illuminate\Http\Request;

class CalendarioLaboralAdminController extends Controller
{
    public function index()
    {
        return view('admin.permisos-catalogos.calendario-laboral', [
            'areas' => Area::orderBy('nombre')->get(),
            'diasInhabiles' => DiaInhabil::orderBy('fecha', 'desc')->paginate(30),
            'nombresDias' => [
                1 => 'Lun',
                2 => 'Mar',
                3 => 'Mié',
                4 => 'Jue',
                5 => 'Vie',
                6 => 'Sáb',
                7 => 'Dom',
            ],
        ]);
    }

    public function updateArea(Request $request, Area $area)
    {
        $validated = $request->validate([
            'dias_laborales' => ['required', 'array', 'min:1'],
            'dias_laborales.*' => ['integer', 'between:1,7'],
        ]);

        $area->update([
            'dias_laborales' => array_values(array_unique(array_map('intval', $validated['dias_laborales']))),
        ]);

        return back()->with('success', 'Horario laboral del área actualizado.');
    }

    public function storeInhabil(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:100'],
        ]);

        DiaInhabil::updateOrCreate(
            ['fecha' => $validated['fecha']],
            [
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'] ?: 'oficial',
                'activo' => true,
            ]
        );

        return back()->with('success', 'Día inhábil guardado correctamente.');
    }

    public function destroyInhabil(DiaInhabil $dia)
    {
        $dia->delete();

        return back()->with('success', 'Día inhábil eliminado.');
    }
}
