<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function dashboard()
    {
        $respuestas = Respuesta::latest()->paginate(20);

        // Estadísticas útiles
        $stats = [
            'total' => Respuesta::count(),
            'hoy' => Respuesta::whereDate('created_at', today())->count(),
            'por_seccion' => Respuesta::selectRaw('count(*) as total, departamento')
                ->groupBy('departamento')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
        ];

        return view('admin.dashboard', compact('respuestas', 'stats'));
    }
public function editField($id)
{
    $field = \App\Models\FormField::findOrFail($id);
    return view('admin.fields.edit', compact('field'));
}
    public function view($id)
    {
        $respuesta = Respuesta::findOrFail($id);
        $data = json_decode($respuesta->data, true);

        return view('admin.view', compact('data', 'respuesta'));
    }

    public function fields()
    {
        $fields = FormField::orderBy('section')
            ->orderBy('id')
            ->get()
            ->groupBy('section');

        return view('admin.fields.fields', compact('fields'));
    }

    public function storeField(Request $req)
    {
        $validated = $req->validate([
            'name' => 'required|string|max:255|unique:form_fields,name',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,select,radio,checkbox',
            'required' => 'boolean',
            'visible' => 'boolean',
            'data_source' => 'nullable|string',
            'data_table' => 'nullable|string',
            'section' => 'required|string|max:50'
        ]);

        FormField::create($validated);
        Cache::forget('form_fields_active');

        return back()->with('success', 'Campo creado exitosamente');
    }
public function update(Request $req, $id)
{
    $respuesta = Respuesta::findOrFail($id);

    $data = $req->except('_token');

    //convertir arrays (checkbox simulado)
    foreach ($data as $key => $value) {
        if (str_contains($value, ',')) {
            $data[$key] = array_map('trim', explode(',', $value));
        }
    }

    $respuesta->update([
        'data' => $data
    ]);

    return back()->with('success', 'Actualizado correctamente');
}
    public function updateField(Request $req, $id)
    {
        $field = FormField::findOrFail($id);

        $validated = $req->validate([
            'name' => 'required|string|max:255|unique:form_fields,name,' . $id,
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,select,radio,checkbox',
            'required' => 'boolean',
            'visible' => 'boolean',
            'data_source' => 'nullable|string',
            'data_table' => 'nullable|string',
            'section' => 'required|string|max:50'
        ]);

        $field->update($validated);
        Cache::forget('form_fields_active');

        return back()->with('success', 'Campo actualizado');
    }

    public function deleteField($id)
    {
        $field = FormField::findOrFail($id);
        $field->delete();
        Cache::forget('form_fields_active');

        return back()->with('success', 'Campo eliminado');
    }

    // Alternar visibilidad del campo
    public function toggleField($id)
    {
        $field = FormField::findOrFail($id);
        $field->visible = !$field->visible;
        $field->save();
        Cache::forget('form_fields_active');

        return back()->with('success', 'Visibilidad actualizada');
    }
}
