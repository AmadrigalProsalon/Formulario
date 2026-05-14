<?php

namespace App\Imports;

use App\Models\Catalogo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CatalogoImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Catalogo([
            'tipo' => $row['tipo'],
            'valor' => $row['valor']
        ]);
    }
}
