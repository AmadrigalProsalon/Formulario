<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use App\Models\FormField;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // =========================
    // DASHBOARD
    // =========================
    public function dashboard()
    {
        $respuestas = Respuesta::latest()->paginate(20);
        return view('admin.dashboard', compact('respuestas'));
    }

    // =========================
    // VER RESPUESTA
    // =========================
    public function view($id)
    {
        $respuesta = Respuesta::findOrFail($id);
        $data = $respuesta->data;

        return view('admin.view', compact('data'));
    }

    // =========================
    // CAMPOS
    // =========================
    public function fields()
    {
        $fields = FormField::orderBy('section')->orderBy('order')->get();
        return view('admin.fields', compact('fields'));
    }

    public function storeField(Request $req)
    {
        FormField::create($req->all());
        return back();
    }

    public function updateField(Request $req, $id)
    {
        FormField::find($id)->update($req->all());
        return back();
    }

    public function deleteField($id)
    {
        FormField::find($id)->delete();
        return back();
    }
}
