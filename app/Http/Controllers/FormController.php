<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormField;
use App\Models\Respuesta;

class FormController extends Controller
{
public function index()
{
    $fields = \App\Models\FormField::where('visible', 1)
        ->orderBy('section')
        ->get()
        ->groupBy('section');

    return view('form', compact('fields'));
}

    public function store(Request $req)
    {
        Respuesta::create([
            'data' => json_encode($req->all())
        ]);

        return redirect('/')->with('ok', 'Guardado');
    }

public function getData($tipo)
{
    return response()->json(
        \DB::table('catalogos')
            ->where('tipo', $tipo) // 🔥 FILTRA POR TIPO
            ->pluck('valor')
    );
}
}
