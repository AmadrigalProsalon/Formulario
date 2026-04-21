<?php

namespace App\Http\Controllers;

use App\Imports\CatalogoImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function import(Request $request)
    {
        Excel::import(new CatalogoImport, $request->file('archivo'));

        return back()->with('success', 'Excel importado');
    }
}
