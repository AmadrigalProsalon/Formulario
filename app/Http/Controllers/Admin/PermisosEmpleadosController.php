<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Empleado;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PermisosEmpleadosController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::with(['area', 'lider'])->orderBy('nombre');

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%")
                    ->orWhere('puesto', 'like', "%{$q}%");
            });
        }

        return view('admin.permisos-catalogos.empleados.index', [
            'empleados' => $query->paginate(25)->withQueryString(),
            'areas' => Area::orderBy('nombre')->get(),
            'lideres' => Empleado::where('es_lider', true)->orderBy('nombre')->get(),
            'filters' => $request->only(['area_id', 'activo', 'q']),
        ]);
    }

    public function importForm()
    {
        return view('admin.permisos-catalogos.empleados.importar');
    }

    public function import(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $rows = $this->leerArchivo($request->file('archivo')->getRealPath());

        $creados = 0;
        $actualizados = 0;
        $saltados = 0;
        $errores = [];

        foreach ($rows as $i => $row) {
            $row = $this->normalizarFila($row);

            $nombre = trim($row['nombre'] ?? $row['trabajador'] ?? $row['colaborador'] ?? '');
            $correo = trim($row['correo'] ?? $row['correo_trabajador'] ?? $row['email'] ?? '');
            $numero = trim((string) ($row['numero_empleado'] ?? $row['numero'] ?? $row['id_empleado'] ?? ''));
            $areaNombre = trim($row['area'] ?? $row['departamento'] ?? '');
            $puesto = trim($row['puesto'] ?? '');
            $liderNombre = trim($row['lider'] ?? $row['jefe'] ?? $row['nombre_lider'] ?? '');
            $liderCorreo = trim($row['correo_lider'] ?? $row['email_lider'] ?? '');
            $fechaIngreso = trim((string) ($row['fecha_ingreso'] ?? '')) ?: null;
            $activo = $this->boolImport($row['activo'] ?? '1');

            if ($nombre === '' && $correo === '' && $numero === '') {
                $saltados++;
                continue;
            }

            if ($nombre === '') {
                $errores[] = 'Fila ' . ($i + 2) . ': falta nombre.';
                continue;
            }

            $area = null;
            if ($areaNombre !== '') {
                $area = Area::firstOrCreate(['nombre' => $areaNombre], ['activo' => true]);
            }

            $lider = null;
            if ($liderNombre !== '' || $liderCorreo !== '') {
                $lider = Empleado::where(function ($q) use ($liderCorreo, $liderNombre) {
                    if ($liderCorreo !== '') {
                        $q->orWhere('correo', $liderCorreo);
                    }
                    if ($liderNombre !== '') {
                        $q->orWhere('nombre', $liderNombre);
                    }
                })->first();

                if (! $lider) {
                    $lider = Empleado::create([
                        'area_id' => $area?->id,
                        'nombre' => $liderNombre ?: $liderCorreo,
                        'correo' => $liderCorreo ?: null,
                        'puesto' => 'Líder / Jefe',
                        'es_lider' => true,
                        'activo' => true,
                    ]);
                    $creados++;
                } else {
                    $lider->update([
                        'area_id' => $lider->area_id ?: $area?->id,
                        'es_lider' => true,
                        'activo' => true,
                    ]);
                }
            }

            $empleado = Empleado::where(function ($q) use ($correo, $numero) {
                if ($correo !== '') {
                    $q->orWhere('correo', $correo);
                }
                if ($numero !== '') {
                    $q->orWhere('numero_empleado', $numero);
                }
            })->first();

            $data = [
                'area_id' => $area?->id,
                'lider_id' => $lider?->id,
                'numero_empleado' => $numero ?: null,
                'nombre' => $nombre,
                'correo' => $correo ?: null,
                'puesto' => $puesto ?: null,
                'fecha_ingreso' => $fechaIngreso ?: null,
                'activo' => $activo,
            ];

            if ($empleado) {
                $empleado->update($data);
                $actualizados++;
            } else {
                Empleado::create($data);
                $creados++;
            }
        }

        return redirect()
            ->route('admin.permisos.empleados.index')
            ->with('success', "Importación finalizada. Creados: {$creados}. Actualizados: {$actualizados}. Saltados: {$saltados}." . (count($errores) ? ' Errores: ' . implode(' | ', array_slice($errores, 0, 5)) : ''));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'lider_id' => ['nullable', 'exists:empleados,id'],
            'numero_empleado' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'es_lider' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['es_lider'] = $request->boolean('es_lider');
        $validated['activo'] = $request->boolean('activo');

        $empleado->update($validated);

        return back()->with('success', 'Empleado actualizado correctamente.');
    }

    private function leerArchivo(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return [];
        }

        $headers = array_shift($rows);
        $headers = array_map(fn ($h) => $this->normalizarHeader((string) $h), $headers);

        $data = [];
        foreach ($rows as $row) {
            $item = [];
            foreach ($headers as $col => $header) {
                if ($header !== '') {
                    $item[$header] = $row[$col] ?? null;
                }
            }
            $data[] = $item;
        }

        return $data;
    }

    private function normalizarFila(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            $out[$this->normalizarHeader((string) $key)] = is_string($value) ? trim($value) : $value;
        }
        return $out;
    }

    private function normalizarHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }

    private function boolImport($value): bool
    {
        $value = mb_strtolower(trim((string) $value));
        return in_array($value, ['1', 'si', 'sí', 'yes', 'true', 'activo', 'activa', ''], true);
    }
}
