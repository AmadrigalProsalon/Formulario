<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerfilPuestoAutofillController extends Controller
{
    private function tableName(): ?string
    {
        foreach (['perfiles_puesto', 'perfiles_puestos', 'puestos_perfiles'] as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function columns(string $table): array
    {
        return Schema::getColumnListing($table);
    }

    private function firstColumn(array $columns, array $options): ?string
    {
        foreach ($options as $option) {
            if (in_array($option, $columns, true)) {
                return $option;
            }
        }

        return null;
    }

    private function firstColumnContaining(array $columns, array $needles): ?string
    {
        foreach ($columns as $column) {
            $normalized = strtolower($column);

            foreach ($needles as $needle) {
                if (str_contains($normalized, strtolower($needle))) {
                    return $column;
                }
            }
        }

        return null;
    }

    private function departamentoColumn(array $columns): ?string
    {
        return $this->firstColumn($columns, [
            'departamento',
            'departamento_solicitante',
            'area',
            'area_departamento',
            'area_departamento_puesto',
            'departamento_area',
            'departamento_puesto',
            'area_puesto',
            'unidad',
            'division',
        ]) ?? $this->firstColumnContaining($columns, [
            'departamento',
            'area',
        ]);
    }

    private function puestoColumn(array $columns): ?string
    {
        return $this->firstColumn($columns, [
            'nombre_puesto',
            'puesto',
            'titulo',
            'nombre',
            'perfil',
            'cargo',
            'posicion',
            'posición',
        ]) ?? $this->firstColumnContaining($columns, [
            'puesto',
            'cargo',
            'perfil',
        ]);
    }

    private function value($row, array $columns)
    {
        foreach ($columns as $column) {
            if (isset($row->{$column}) && $row->{$column} !== null && trim((string) $row->{$column}) !== '') {
                return $row->{$column};
            }
        }

        return null;
    }

    public function departamentos()
    {
        $table = $this->tableName();

        if (! $table) {
            return response()->json([
                'data' => [],
                'message' => 'No existe la tabla perfiles_puesto.',
            ]);
        }

        $columns = $this->columns($table);
        $departamentoColumn = $this->departamentoColumn($columns);

        if (! $departamentoColumn) {
            return response()->json([
                'data' => ['General'],
                'message' => 'No se encontró columna de departamento; se usará General.',
            ]);
        }

        $departamentos = DB::table($table)
            ->whereNotNull($departamentoColumn)
            ->where($departamentoColumn, '<>', '')
            ->distinct()
            ->orderBy($departamentoColumn)
            ->pluck($departamentoColumn)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($departamentos->isEmpty()) {
            $departamentos = collect(['General']);
        }

        return response()->json([
            'data' => $departamentos,
            'table' => $table,
            'departamento_column' => $departamentoColumn,
        ]);
    }

    public function perfiles(Request $request)
    {
        $table = $this->tableName();

        if (! $table) {
            return response()->json([
                'data' => [],
                'message' => 'No existe la tabla perfiles_puesto.',
            ]);
        }

        $columns = $this->columns($table);

        $departamentoColumn = $this->departamentoColumn($columns);
        $puestoColumn = $this->puestoColumn($columns);

        if (! $puestoColumn) {
            return response()->json([
                'data' => [],
                'message' => 'No se encontró columna de puesto en perfiles_puesto.',
                'columns' => $columns,
            ]);
        }

        $query = DB::table($table);

        $departamento = $request->input('departamento');

        if (
            $departamentoColumn &&
            $departamento &&
            $departamento !== 'General'
        ) {
            $query->where($departamentoColumn, $departamento);
        }

        $perfiles = $query
            ->orderBy($puestoColumn)
            ->get()
            ->map(function ($row) use ($puestoColumn, $departamentoColumn) {
                return [
                    'id' => $row->id,
                    'nombre' => $row->{$puestoColumn} ?? 'Perfil sin nombre',
                    'departamento' => $departamentoColumn ? ($row->{$departamentoColumn} ?? 'General') : 'General',
                ];
            })
            ->values();

        return response()->json([
            'data' => $perfiles,
            'table' => $table,
            'puesto_column' => $puestoColumn,
            'departamento_column' => $departamentoColumn,
        ]);
    }

    public function show($id)
    {
        $table = $this->tableName();

        if (! $table) {
            return response()->json([
                'message' => 'No existe la tabla perfiles_puesto.',
            ], 404);
        }

        $row = DB::table($table)->where('id', $id)->first();

        if (! $row) {
            return response()->json([
                'message' => 'Perfil no encontrado.',
            ], 404);
        }

        $columns = $this->columns($table);

        $departamentoColumn = $this->departamentoColumn($columns);
        $puestoColumn = $this->puestoColumn($columns);

        $data = [
            'id' => $row->id,

            'departamento' => $departamentoColumn
                ? ($row->{$departamentoColumn} ?? 'General')
                : 'General',

            'nombre_puesto' => $puestoColumn
                ? ($row->{$puestoColumn} ?? null)
                : null,

            'area_departamento_puesto' => $this->value($row, [
                'area_departamento_puesto',
                'area_departamento',
                'departamento_area',
                'area_puesto',
                'departamento_puesto',
                'departamento',
                'area',
            ]),

            'puesto_reporta' => $this->value($row, [
                'puesto_reporta',
                'puesto_al_que_reporta',
                'reporta_a',
                'reporta',
                'jefe_directo',
                'lider',
                'líder',
            ]),

            'funciones_generales' => $this->value($row, [
                'funciones_generales',
                'funciones',
                'actividades',
                'responsabilidades',
                'descripcion',
                'descripción',
                'objetivo',
            ]),

            'escolaridad' => $this->value($row, [
                'escolaridad',
                'educacion',
                'educación',
                'nivel_estudios',
                'estudios',
            ]),

            'area_experiencia' => $this->value($row, [
                'area_experiencia',
                'área_experiencia',
                'experiencia_area',
                'experiencia_en',
                'experiencia',
            ]),

            'anios_experiencia' => $this->value($row, [
                'anios_experiencia',
                'años_experiencia',
                'anos_experiencia',
                'tiempo_experiencia',
            ]),

            'conocimientos_indispensables' => $this->value($row, [
                'conocimientos_indispensables',
                'conocimientos_requeridos',
                'conocimientos',
            ]),

            'conocimientos_deseables' => $this->value($row, [
                'conocimientos_deseables',
            ]),

            'habilidades_indispensables' => $this->value($row, [
                'habilidades_indispensables',
                'habilidades_requeridas',
                'habilidades',
                'competencias',
            ]),

            'habilidades_deseables' => $this->value($row, [
                'habilidades_deseables',
            ]),

            'software_especifico' => $this->value($row, [
                'software_especifico',
                'software',
                'herramientas_software',
            ]),

            'hardware_requerido' => $this->value($row, [
                'hardware_requerido',
                'hardware',
                'equipo',
                'herramientas_hardware',
            ]),

            'nivel_ingles' => $this->value($row, [
                'nivel_ingles',
                'nivel_inglés',
                'ingles',
                'inglés',
                'idioma_ingles',
            ]),
        ];

        return response()->json([
            'data' => $data,
            'table' => $table,
            'columns' => $columns,
        ]);
    }
}
