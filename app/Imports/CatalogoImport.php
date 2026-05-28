<?php

namespace App\Imports;

use App\Models\Catalogo;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CatalogoImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $tipo = trim((string) ($row['tipo'] ?? ''));
        $valor = trim((string) ($row['valor'] ?? ''));

        if ($tipo === '' || $valor === '') {
            return null;
        }

        $tipo = Str::slug($tipo, '_');

        return Catalogo::updateOrCreate(
            [
                'tipo' => $tipo,
                'valor' => $valor,
            ],
            [
                'tipo' => $tipo,
                'valor' => $valor,
            ]
        );
    }
}
