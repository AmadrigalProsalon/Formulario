<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PerfilPuestoCsvController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $departamento = trim((string) $request->query('departamento', ''));

        $departamentos = DB::table('perfiles_puesto')
            ->where('activo', 1)
            ->whereNotNull('area_departamento')
            ->where('area_departamento', '!=', '')
            ->distinct()
            ->orderBy('area_departamento')
            ->pluck('area_departamento');

        $perfiles = DB::table('perfiles_puesto')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre_puesto', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%")
                        ->orWhere('area_departamento', 'like', "%{$q}%")
                        ->orWhere('puesto_reporta', 'like', "%{$q}%");
                });
            })
            ->when($departamento, fn ($query) => $query->where('area_departamento', $departamento))
            ->orderBy('area_departamento')
            ->orderBy('nombre_puesto')
            ->paginate(25)
            ->withQueryString();

        return view('admin.perfiles-puesto.csv', compact('perfiles', 'departamentos', 'q', 'departamento'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ], [
            'archivo.required' => 'Selecciona un archivo CSV.',
            'archivo.mimes' => 'El archivo debe ser CSV.',
        ]);

        $path = $request->file('archivo')->store('perfiles-puesto/csv', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $resultado = $this->importarCsv($fullPath, $path);

        return redirect()
            ->route('admin.perfiles-puesto.csv')
            ->with('success', "Importación completada. Creados: {$resultado['creados']}. Actualizados: {$resultado['actualizados']}. Omitidos: {$resultado['omitidos']}.");
    }

    public function descargarPlantilla()
    {
        $headers = [
            'codigo',
            'nombre_puesto',
            'area_departamento',
            'puesto_reporta',
            'ubicacion',
            'horario',
            'descripcion_puesto',
            'objetivo_puesto',
            'requerimientos_minimos',
            'escolaridad_detectada',
            'experiencia_detectada',
            'ingles_detectado',
            'software_detectado',
            'hardware_detectado',
            'cualidades',
            'habilidades',
            'responsabilidades',
            'activo',
        ];

        $rows = [
            $headers,
            [
                'ATC-001',
                'Atención al Cliente',
                'ATENCIÓN AL CLIENTE',
                'Gerente de Marketing',
                'Oficinas',
                'Lunes a viernes de 8:00 a 18:00',
                'Brinda atención y seguimiento a clientes.',
                'Garantizar la satisfacción del cliente.',
                'Educación técnica o universitaria. Experiencia mínima de 2 años.',
                'Carrera técnica o Licenciatura',
                'Mínimo 2 años',
                'Intermedio',
                'CRM, Office, Desk',
                'Computadora, diadema',
                'Empatía, paciencia, responsabilidad',
                'Comunicación verbal y escrita, resolución de problemas',
                'Atender consultas, resolver problemas, registrar interacciones y dar seguimiento.',
                '1',
            ],
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'plantilla_perfiles_puesto.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function departamentosApi()
    {
        $departamentos = DB::table('perfiles_puesto')
            ->where('activo', 1)
            ->whereNotNull('area_departamento')
            ->where('area_departamento', '!=', '')
            ->distinct()
            ->orderBy('area_departamento')
            ->pluck('area_departamento');

        return response()->json($departamentos);
    }

    public function porDepartamento(Request $request)
    {
        $departamento = trim((string) $request->query('departamento', ''));
        $q = trim((string) $request->query('q', ''));

        $perfiles = DB::table('perfiles_puesto')
            ->where('activo', 1)
            ->when($departamento, fn ($query) => $query->where('area_departamento', $departamento))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre_puesto', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%")
                        ->orWhere('puesto_reporta', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre_puesto')
            ->limit(50)
            ->get([
                'id',
                'codigo',
                'nombre_puesto',
                'area_departamento',
                'puesto_reporta',
            ]);

        return response()->json($perfiles);
    }

    public function showApi(int $perfil)
    {
        $perfil = DB::table('perfiles_puesto')->where('id', $perfil)->first();

        abort_if(! $perfil, 404);

        return response()->json($perfil);
    }

    private function importarCsv(string $fullPath, string $storedPath): array
    {
        $handle = fopen($fullPath, 'r');

        if (! $handle) {
            return ['creados' => 0, 'actualizados' => 0, 'omitidos' => 1];
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);
            return ['creados' => 0, 'actualizados' => 0, 'omitidos' => 1];
        }

        $header = array_map(fn ($item) => $this->normalizarHeader($item), $header);

        $creados = 0;
        $actualizados = 0;
        $omitidos = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->filaVacia($row)) {
                continue;
            }

            $data = [];
            foreach ($header as $index => $key) {
                $data[$key] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }

            $nombre = $this->firstValue($data, ['nombre_puesto', 'puesto', 'nombre', 'perfil']);
            $area = $this->firstValue($data, ['area_departamento', 'departamento', 'area']);

            if (! $nombre) {
                $omitidos++;
                continue;
            }

            $codigo = $this->firstValue($data, ['codigo', 'id', 'clave']);
            $uniqueKey = $this->crearUniqueKey($codigo, $nombre, $area);

            $payload = [
                'unique_key' => $uniqueKey,
                'codigo' => $codigo,
                'nombre_puesto' => $nombre,
                'slug' => Str::slug($nombre . '-' . ($area ?: 'general')),
                'area_departamento' => $area,
                'puesto_reporta' => $this->firstValue($data, ['puesto_reporta', 'reporta', 'jefe', 'puesto_a_quien_reporta']),
                'descripcion_puesto' => $this->firstValue($data, ['descripcion_puesto', 'descripcion']),
                'objetivo_puesto' => $this->firstValue($data, ['objetivo_puesto', 'objetivo']),
                'requerimientos_minimos' => $this->firstValue($data, ['requerimientos_minimos', 'requerimientos']),
                'cualidades' => $this->firstValue($data, ['cualidades']),
                'habilidades' => $this->firstValue($data, ['habilidades']),
                'responsabilidades' => $this->firstValue($data, ['responsabilidades', 'funciones', 'funciones_generales']),
                'escolaridad_detectada' => $this->firstValue($data, ['escolaridad_detectada', 'escolaridad']),
                'experiencia_detectada' => $this->firstValue($data, ['experiencia_detectada', 'experiencia']),
                'ingles_detectado' => $this->firstValue($data, ['ingles_detectado', 'nivel_ingles', 'ingles']),
                'software_detectado' => $this->firstValue($data, ['software_detectado', 'software']),
                'hardware_detectado' => $this->firstValue($data, ['hardware_detectado', 'hardware']),
                'archivo_original_path' => $storedPath,
                'texto_original' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'datos_extra' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'activo' => $this->parseBoolean($this->firstValue($data, ['activo'], '1')),
                'importado_at' => now(),
                'updated_at' => now(),
            ];

            $exists = DB::table('perfiles_puesto')->where('unique_key', $uniqueKey)->exists();

            if ($exists) {
                DB::table('perfiles_puesto')->where('unique_key', $uniqueKey)->update($payload);
                $actualizados++;
            } else {
                $payload['created_at'] = now();
                DB::table('perfiles_puesto')->insert($payload);
                $creados++;
            }
        }

        fclose($handle);

        return compact('creados', 'actualizados', 'omitidos');
    }

    private function normalizarHeader(?string $header): string
    {
        $header = trim((string) $header);
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

        return Str::of($header)
            ->lower()
            ->ascii()
            ->replace(['/', '-', '.', '  '], ['_', '_', '', ' '])
            ->replaceMatches('/[^a-z0-9_ ]/', '')
            ->replace(' ', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();
    }

    private function filaVacia(array $row): bool
    {
        return collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function firstValue(array $data, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return $data[$key];
            }
        }

        return $default;
    }

    private function crearUniqueKey(?string $codigo, string $nombre, ?string $area): string
    {
        if ($codigo) {
            return Str::slug($codigo);
        }

        return Str::slug(($area ?: 'general') . '-' . $nombre);
    }

    private function parseBoolean(mixed $value): int
    {
        $value = Str::lower(trim((string) $value));

        return in_array($value, ['1', 'si', 'sí', 'true', 'activo', 'yes'], true) ? 1 : 0;
    }
}
