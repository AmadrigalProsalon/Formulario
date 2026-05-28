<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Catalogo::query()
            ->orderBy('tipo')
            ->orderBy('valor');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);

            $query->where(function ($subquery) use ($q) {
                $subquery
                    ->where('tipo', 'like', "%{$q}%")
                    ->orWhere('valor', 'like', "%{$q}%");
            });
        }

        $catalogos = $query
            ->paginate(30)
            ->withQueryString();

        $tipos = Catalogo::select('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        $filters = $request->only(['tipo', 'q']);

        return view('admin.catalogos.index', compact('catalogos', 'tipos', 'filters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'valor' => ['required', 'string', 'max:255'],
        ]);

        $tipo = Str::slug($validated['tipo'], '_');
        $valor = trim($validated['valor']);

        Catalogo::updateOrCreate([
            'tipo' => $tipo,
            'valor' => $valor,
        ]);

        Cache::forget("catalogos_{$tipo}");

        return redirect()
            ->route('admin.catalogos.index', ['tipo' => $tipo])
            ->with('success', 'Catálogo guardado correctamente.');
    }

    public function update(Request $request, Catalogo $catalogo)
    {
        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'valor' => ['required', 'string', 'max:255'],
        ]);

        $oldTipo = $catalogo->tipo;

        $catalogo->update([
            'tipo' => Str::slug($validated['tipo'], '_'),
            'valor' => trim($validated['valor']),
        ]);

        Cache::forget("catalogos_{$oldTipo}");
        Cache::forget("catalogos_{$catalogo->tipo}");

        return redirect()
            ->route('admin.catalogos.index', ['tipo' => $catalogo->tipo])
            ->with('success', 'Catálogo actualizado correctamente.');
    }

    public function destroy(Catalogo $catalogo)
    {
        $tipo = $catalogo->tipo;

        $catalogo->delete();

        Cache::forget("catalogos_{$tipo}");

        return redirect()
            ->route('admin.catalogos.index', ['tipo' => $tipo])
            ->with('success', 'Catálogo eliminado correctamente.');
    }
}
