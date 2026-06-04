<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicionPersonal extends Model
{
    protected $table = 'requisiciones_personal';

    protected $fillable = [
        'formulario_id',
        'respuesta_id',
        'perfil_puesto_id',
        'folio',
        'nombre_puesto',
        'departamento',
        'causa_vacante',
        'tipo_contrato',
        'estatus',
        'observaciones_rh',
    ];

    public function perfil(): BelongsTo
    {
        return $this->belongsTo(PerfilPuesto::class, 'perfil_puesto_id');
    }

    public function respuesta(): BelongsTo
    {
        return $this->belongsTo(Respuesta::class, 'respuesta_id');
    }

    public function formulario(): BelongsTo
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }
}
