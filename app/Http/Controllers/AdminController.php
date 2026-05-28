<?php

namespace App\Http\Controllers;

use App\Models\FormField;
use App\Models\Formulario;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = $this->respuestasQuery($request);

        $respuestas = $query
            ->with('formulario')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Respuesta::count(),
            'hoy' => Respuesta::whereDate('created_at', today())->count(),
            'semana' => Respuesta::where('created_at', '>=', now()->subDays(7))->count(),
            'formularios' => Formulario::count(),
        ];

        $formularios = Formulario::orderBy('titulo')->get();

        $departamentos = Respuesta::whereNotNull('departamento')
            ->where('departamento', '!=', '')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento');

        $filters = $request->only(['q', 'desde', 'hasta', 'departamento', 'formulario_id']);

        return view('admin.dashboard', compact('respuestas', 'stats', 'departamentos', 'filters', 'formularios'));
    }

    public function exportRespuestas(Request $request)
    {
        $rows = $this->respuestasQuery($request)
            ->with('formulario')
            ->latest()
            ->get();

        $dynamicHeaders = $rows
            ->flatMap(function ($respuesta) {
                $data = $this->normalizarData($respuesta->data);
                return array_keys($data);
            })
            ->unique()
            ->reject(fn ($key) => in_array($key, ['departamento', 'puesto', 'horario']))
            ->values()
            ->toArray();

        $headers = array_merge([
            'id',
            'formulario',
            'fecha',
            'departamento',
            'puesto',
            'horario',
            'ip',
        ], $dynamicHeaders);

        $fileName = 'respuestas_rh_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows, $headers, $dynamicHeaders) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $respuesta) {
                $data = $this->normalizarData($respuesta->data);

                $line = [
                    $respuesta->id,
                    $respuesta->formulario?->titulo,
                    optional($respuesta->created_at)->format('Y-m-d H:i:s'),
                    $respuesta->departamento,
                    $respuesta->puesto,
                    $respuesta->horario,
                    $respuesta->ip,
                ];

                foreach ($dynamicHeaders as $header) {
                    $value = $data[$header] ?? '';

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $line[] = $value;
                }

                fputcsv($handle, $line);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function view($id)
    {
        $respuesta = Respuesta::with('formulario')->findOrFail($id);
        $data = $this->normalizarData($respuesta->data);

        return view('admin.view', compact('data', 'respuesta'));
    }

    public function update(Request $request, $id)
    {
        $respuesta = Respuesta::findOrFail($id);

        $data = collect($request->except('_token', '_method'))
            ->map(function ($value) {
                if (is_array($value)) {
                    return implode(', ', array_filter($value));
                }

                return is_string($value) ? trim($value) : $value;
            })
            ->toArray();

        $respuesta->update([
            'data' => $data,
            'departamento' => $data['departamento'] ?? $respuesta->departamento,
            'puesto' => $data['puesto'] ?? $respuesta->puesto,
            'horario' => $data['horario'] ?? $respuesta->horario,
        ]);

        return redirect()
            ->route('admin.respuesta.view', $respuesta->id)
            ->with('success', 'Respuesta actualizada correctamente.');
    }

    public function fields(Request $request)
    {
        $formularios = Formulario::orderBy('titulo')->get();

        $formulario = null;

        if ($request->filled('formulario_id')) {
            $formulario = Formulario::find($request->formulario_id);
        }

        $formulario = $formulario
            ?: Formulario::default()->first()
            ?: $formularios->first();

        $fields = collect();

        if ($formulario) {
            $fields = FormField::where('formulario_id', $formulario->id)
                ->orderBy('section')
                ->orderBy('id')
                ->get()
                ->groupBy('section');
        }

        return view('admin.fields.fields', compact('fields', 'formularios', 'formulario'));
    }

    public function editField($id)
    {
        $field = FormField::findOrFail($id);
        $formularios = Formulario::orderBy('titulo')->get();

        return view('admin.fields.edit', compact('field', 'formularios'));
    }

    public function storeField(Request $request)
    {
        $validated = $this->validateField($request);

        FormField::create($validated);

        Cache::forget('form_fields_active');

        return redirect()
            ->route('admin.fields.index', ['formulario_id' => $validated['formulario_id']])
            ->with('success', 'Campo creado correctamente.');
    }

    public function updateField(Request $request, $id)
    {
        $field = FormField::findOrFail($id);

        $validated = $this->validateField($request, $field->id);

        $field->update($validated);

        Cache::forget('form_fields_active');

        return redirect()
            ->route('admin.fields.index', ['formulario_id' => $validated['formulario_id']])
            ->with('success', 'Campo actualizado correctamente.');
    }

    public function deleteField($id)
    {
        $field = FormField::findOrFail($id);
        $formularioId = $field->formulario_id;

        $field->delete();

        Cache::forget('form_fields_active');

        return redirect()
            ->route('admin.fields.index', ['formulario_id' => $formularioId])
            ->with('success', 'Campo eliminado correctamente.');
    }

    public function toggleField($id)
    {
        $field = FormField::findOrFail($id);

        $field->update([
            'visible' => ! $field->visible,
        ]);

        Cache::forget('form_fields_active');

        return redirect()
            ->route('admin.fields.index', ['formulario_id' => $field->formulario_id])
            ->with('success', 'Visibilidad actualizada.');
    }

    private function respuestasQuery(Request $request)
    {
        $query = Respuesta::query();

        if ($request->filled('formulario_id')) {
            $query->where('formulario_id', $request->formulario_id);
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);

            $query->where(function ($subquery) use ($q) {
                if (is_numeric($q)) {
                    $subquery->orWhere('id', $q);
                }

                $subquery
                    ->orWhere('departamento', 'like', "%{$q}%")
                    ->orWhere('puesto', 'like', "%{$q}%")
                    ->orWhere('horario', 'like', "%{$q}%")
                    ->orWhere('data', 'like', "%{$q}%");
            });
        }

        return $query;
    }

    private function validateField(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'formulario_id' => ['required', 'exists:formularios,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('form_fields', 'name')
                    ->where(fn ($query) => $query->where('formulario_id', $request->formulario_id))
                    ->ignore($ignoreId),
            ],
            'label' => ['required', 'string', 'max:255'],
            'type' => [
                'required',
                Rule::in([
                    'text',
                    'textarea',
                    'select',
                    'radio',
                    'checkbox',
                    'email',
                    'number',
                    'date',
                    'tel',
                ]),
            ],
            'required' => ['nullable', 'boolean'],
            'visible' => ['nullable', 'boolean'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'data_table' => ['nullable', 'string', 'max:255'],
            'section' => ['required', 'string', 'max:50'],
        ]);

        $validated['required'] = $request->boolean('required');
        $validated['visible'] = $request->boolean('visible');

        return $validated;
    }

    private function normalizarData($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data)) {
            return json_decode($data, true) ?: [];
        }

        return [];
    }
}
