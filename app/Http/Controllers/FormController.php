<?php

namespace App\Http\Controllers;

use App\Mail\NuevaRespuesta;
use App\Models\FormField;
use App\Models\Formulario;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpWord\TemplateProcessor;
use Throwable;

class FormController extends Controller
{
    public function index(?Formulario $formulario = null)
    {
        $formulario = $formulario ?: $this->getDefaultFormulario();

        if (! $formulario || ! $formulario->activo) {
            abort(404, 'Formulario no disponible.');
        }

        $fields = FormField::where('formulario_id', $formulario->id)
            ->where('visible', 1)
            ->orderBy('section')
            ->orderBy('id')
            ->get()
            ->groupBy('section');

        $catalogos = $this->getCatalogosParaCampos($fields);

        return view('form', compact('formulario', 'fields', 'catalogos'));
    }

    public function storeDefault(Request $request)
    {
        $formulario = $this->getDefaultFormulario();

        if (! $formulario) {
            abort(404, 'No hay formulario predeterminado.');
        }

        return $this->store($request, $formulario);
    }

    public function store(Request $request, Formulario $formulario)
    {
        if (! $formulario->activo) {
            abort(404, 'Formulario no disponible.');
        }

        $rateLimitKey = 'form_submit_' . $formulario->id . '_' . $request->ip();

        if (Cache::has($rateLimitKey)) {
            return redirect()
                ->route('form.show', $formulario)
                ->with('error', 'Por favor espera 30 segundos antes de enviar otro formulario.')
                ->withInput();
        }

        $fields = FormField::where('formulario_id', $formulario->id)
            ->where('visible', 1)
            ->get();

        if ($fields->isEmpty()) {
            return redirect()
                ->route('form.show', $formulario)
                ->with('error', 'Este formulario todavía no tiene campos configurados.');
        }

        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];

            if ($field->required) {
                $fieldRules[] = $field->type === 'checkbox' ? 'array' : 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field->type === 'checkbox') {
                $rules[$field->name] = $fieldRules;
                $rules[$field->name . '.*'] = ['nullable', 'string', 'max:255'];
            } elseif ($field->type === 'email') {
                $rules[$field->name] = array_merge($fieldRules, ['email', 'max:255']);
            } elseif ($field->type === 'number') {
                $rules[$field->name] = array_merge($fieldRules, ['numeric']);
            } elseif ($field->type === 'date') {
                $rules[$field->name] = array_merge($fieldRules, ['date']);
            } elseif ($field->type === 'textarea') {
                $rules[$field->name] = array_merge($fieldRules, ['string', 'max:5000']);
            } else {
                $rules[$field->name] = array_merge($fieldRules, ['string', 'max:255']);
            }
        }

        $inputPermitido = $request->only($fields->pluck('name')->toArray());

        $validator = Validator::make($inputPermitido, $rules, [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser un correo válido.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'array' => 'El campo :attribute debe ser una lista válida.',
            'max' => 'El campo :attribute es demasiado largo.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('form.show', $formulario)
                ->withErrors($validator)
                ->withInput();
        }

        $datosFiltrados = $validator->validated();

        foreach ($datosFiltrados as $key => $value) {
            if (is_array($value)) {
                $datosFiltrados[$key] = implode(', ', array_filter($value));
            }
        }

        try {
            $respuesta = Respuesta::create([
                'formulario_id' => $formulario->id,
                'data' => $datosFiltrados,
                'departamento' => $datosFiltrados['departamento'] ?? null,
                'puesto' => $datosFiltrados['puesto'] ?? null,
                'horario' => $datosFiltrados['horario'] ?? null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Cache::put($rateLimitKey, true, 30);

            $filePath = $this->generarWordSiExiste($formulario, $respuesta, $datosFiltrados);

            Mail::to($formulario->mail_to ?: config('rh.mail_to'))
                ->send(new NuevaRespuesta($datosFiltrados, $filePath, $formulario));

            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
            }

            return redirect()->route('form.gracias');
        } catch (Throwable $e) {
            Log::error('Error al guardar formulario RH', [
                'formulario_id' => $formulario->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('form.show', $formulario)
                ->with('error', 'Ocurrió un error al procesar el formulario. Contacta al área de sistemas.')
                ->withInput();
        }
    }

    private function generarWordSiExiste(Formulario $formulario, Respuesta $respuesta, array $datos): ?string
    {
        $templatePath = $formulario->template_path ?: config('rh.template_path');

        if (! $templatePath || ! file_exists($templatePath)) {
            return null;
        }

        $template = new TemplateProcessor($templatePath);

        foreach ($datos as $key => $value) {
            $template->setValue($key, e((string) ($value ?? '')));
        }

        $fileName = $formulario->slug . '_' . $respuesta->id . '_' . now()->format('Ymd_His') . '.docx';
        $filePath = storage_path('app/' . $fileName);

        $template->saveAs($filePath);

        return $filePath;
    }

    private function getDefaultFormulario(): ?Formulario
    {
        return Formulario::default()->first()
            ?: Formulario::activos()->oldest()->first();
    }

    private function getCatalogosParaCampos($fields): array
    {
        $tipos = collect($fields)
            ->flatten()
            ->filter(function ($field) {
                $source = strtolower(trim($field->data_source ?? ''));

                return in_array($source, ['catalogos', 'db', 'database'])
                    && ! empty($field->data_table);
            })
            ->pluck('data_table')
            ->map(fn ($tipo) => trim($tipo))
            ->filter()
            ->unique()
            ->values();

        if ($tipos->isEmpty()) {
            return [];
        }

        return DB::table('catalogos')
            ->whereIn('tipo', $tipos)
            ->orderByRaw("
                CASE
                    WHEN LOWER(valor) = 'otro' THEN 1
                    ELSE 0
                END
            ")
            ->orderBy('valor')
            ->get()
            ->groupBy('tipo')
            ->map(fn ($items) => $items->pluck('valor')->toArray())
            ->toArray();
    }

    public function getData(string $tipo)
    {
        $tipoExiste = DB::table('catalogos')
            ->where('tipo', $tipo)
            ->exists();

        if (! $tipoExiste) {
            return response()->json(['error' => 'Tipo no válido'], 400);
        }

        $data = Cache::remember("catalogos_{$tipo}", 86400, function () use ($tipo) {
            return DB::table('catalogos')
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
