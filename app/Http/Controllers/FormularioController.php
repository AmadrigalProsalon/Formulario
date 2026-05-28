<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormularioController extends Controller
{
    public function index()
    {
        $formularios = Formulario::withCount(['fields', 'respuestas'])
            ->latest()
            ->paginate(20);

        return view('admin.formularios.index', compact('formularios'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateFormulario($request);

        $slug = $validated['slug'] ?: Str::slug($validated['titulo']);

        Formulario::create([
            'titulo' => $validated['titulo'],
            'slug' => $slug,
            'descripcion' => $validated['descripcion'] ?? null,
            'mail_to' => $validated['mail_to'] ?? null,
            'template_path' => $validated['template_path'] ?? null,
            'activo' => $request->boolean('activo'),
            'es_default' => false,
        ]);

        Cache::forget('form_fields_active');

        return redirect()
            ->route('admin.formularios.index')
            ->with('success', 'Formulario creado correctamente.');
    }

    public function update(Request $request, Formulario $formulario)
    {
        $validated = $this->validateFormulario($request, $formulario->id);

        $slug = $validated['slug'] ?: Str::slug($validated['titulo']);

        $formulario->update([
            'titulo' => $validated['titulo'],
            'slug' => $slug,
            'descripcion' => $validated['descripcion'] ?? null,
            'mail_to' => $validated['mail_to'] ?? null,
            'template_path' => $validated['template_path'] ?? null,
            'activo' => $request->boolean('activo'),
        ]);

        Cache::forget('form_fields_active');

        return redirect()
            ->route('admin.formularios.index')
            ->with('success', 'Formulario actualizado correctamente.');
    }

    public function destroy(Formulario $formulario)
    {
        if ($formulario->es_default) {
            return back()->with('error', 'No puedes eliminar el formulario predeterminado.');
        }

        if ($formulario->respuestas()->exists()) {
            return back()->with('error', 'No puedes eliminar un formulario que ya tiene respuestas.');
        }

        $formulario->fields()->delete();
        $formulario->delete();

        Cache::forget('form_fields_active');

        return redirect()
            ->route('admin.formularios.index')
            ->with('success', 'Formulario eliminado correctamente.');
    }

    public function toggle(Formulario $formulario)
    {
        if ($formulario->es_default && $formulario->activo) {
            return back()->with('error', 'No puedes desactivar el formulario predeterminado.');
        }

        $formulario->update([
            'activo' => ! $formulario->activo,
        ]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function makeDefault(Formulario $formulario)
    {
        DB::transaction(function () use ($formulario) {
            Formulario::query()->update(['es_default' => false]);

            $formulario->update([
                'es_default' => true,
                'activo' => true,
            ]);
        });

        return back()->with('success', 'Formulario predeterminado actualizado.');
    }

    private function validateFormulario(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('formularios', 'slug')->ignore($ignoreId),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'mail_to' => ['nullable', 'email', 'max:255'],
            'template_path' => ['nullable', 'string', 'max:500'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }
}
