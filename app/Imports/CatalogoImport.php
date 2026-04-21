<?php

namespace App\Imports;

use App\Models\Catalogo;
use Maatwebsite\Excel\Concerns\ToModel;

class CatalogoImport implements ToModel
{
    public function model(array $row)
    {
        return new Catalogo([
            'tipo' => 'departamento', // puedes hacerlo dinámico luego
            'valor' => $row[0],
        ]);
    }
}
