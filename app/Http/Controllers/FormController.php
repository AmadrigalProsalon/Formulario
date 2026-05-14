<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormField;
use App\Models\Respuesta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevaRespuesta;
use PhpOffice\PhpWord\TemplateProcessor;


class FormController extends Controller
{
    public function index()
    {
        $fields = Cache::remember('form_fields_active', 3600, function () {
            return FormField::where('visible', 1)
                ->orderBy('section')
                ->orderBy('id')
                ->get()
                ->groupBy('section');
        });

        return view('form', compact('fields'));
    }

public function store(Request $req)
{
    $key = 'form_submit_' . $req->ip();

    if (Cache::has($key)) {
        return redirect('/')->with('error', 'Por favor espera 30 segundos');
    }

    $camposValidos = FormField::where('visible', 1)->pluck('name')->toArray();
    $datosFiltrados = $req->only($camposValidos);

    $camposRequeridos = FormField::where('visible', 1)
        ->where('required', 1)
        ->pluck('name')
        ->toArray();

    $validator = Validator::make($datosFiltrados, array_fill_keys($camposRequeridos, 'required'));

    if ($validator->fails()) {
        return redirect('/')->withErrors($validator)->withInput();
    }

    foreach ($datosFiltrados as $k => $value) {
        if (is_array($value)) {
            $datosFiltrados[$k] = implode(', ', $value);
        }
    }

// ==========================
// GUARDAR RESPUESTA
// ==========================
$respuesta = Respuesta::create([
    'data' => json_encode($datosFiltrados, JSON_UNESCAPED_UNICODE),
    'departamento' => $datosFiltrados['departamento'] ?? null,
    'puesto' => $datosFiltrados['puesto'] ?? null,
    'horario' => $datosFiltrados['horario'] ?? null,
    'ip' => $req->ip(),
    'user_agent' => $req->userAgent()
]);

Cache::put($key, true, 30);

// ==========================
// GENERAR WORD
// ==========================
$template = new TemplateProcessor(
    storage_path('app/templates/plantilla.docx')
);

foreach ($datosFiltrados as $key => $value) {

    if (is_array($value)) {
        $value = implode(', ', $value);
    }

    $template->setValue($key, $value ?? '');
}

$fileName = 'perfil_'.$respuesta->id.'.docx';
$filePath = storage_path('app/'.$fileName);

$template->saveAs($filePath);

// ==========================
// ENVIAR CORREO
// ==========================
Mail::to('amadrigal@prosalon.mx')
    ->send(new NuevaRespuesta($datosFiltrados, $filePath));

// ==========================
// BORRAR ARCHIVO (opcional)
// ==========================
unlink($filePath);

// ==========================
return redirect('/gracias');
}

public function getData($tipo)
{
    // Validar dinámicamente contra BD
    $tipoExiste = \DB::table('catalogos')
        ->where('tipo', $tipo)
        ->exists();

    if (!$tipoExiste) {
        return response()->json(['error' => 'Tipo no válido'], 400);
    }

    // Obtener datos con cache
    $data = Cache::remember("catalogos_{$tipo}", 86400, function() use ($tipo) {
        return \DB::table('catalogos')
            ->where('tipo', $tipo)
            ->orderByRaw("
                CASE
                    WHEN LOWER(valor) = 'otro' THEN 1
                    ELSE 0
                END
            ")
            ->orderBy('valor', 'asc')
            ->pluck('valor');
    });

    return response()->json($data);
}
}
