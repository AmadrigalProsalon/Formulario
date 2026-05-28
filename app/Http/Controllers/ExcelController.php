<?php

namespace App\Http\Controllers;

use App\Imports\CatalogoImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes' => 'El archivo debe ser xlsx, xls o csv.',
            'archivo.max' => 'El archivo no debe pesar más de 10 MB.',
        ]);

        Excel::import(new CatalogoImport, $request->file('archivo'));

        Cache::flush();

        return back()->with('success', 'Excel importado correctamente');
    }
}
