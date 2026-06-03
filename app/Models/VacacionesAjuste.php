<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacacionesAjuste extends Model
{
    protected $table = 'vacaciones_ajustes';

    protected $fillable = [
        'empleado_id',
        'anio',
        'dias',
        'tipo',
        'comentario',
        'created_by',
    ];

    protected $casts = [
        'dias' => 'decimal:2',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
