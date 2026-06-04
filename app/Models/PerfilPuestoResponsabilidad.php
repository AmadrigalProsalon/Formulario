<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilPuestoResponsabilidad extends Model
{
    protected $table = 'perfil_puesto_responsabilidades';

    protected $fillable = [
        'perfil_puesto_id',
        'titulo',
        'descripcion',
        'orden',
    ];

    public function perfil(): BelongsTo
    {
        return $this->belongsTo(PerfilPuesto::class, 'perfil_puesto_id');
    }
}
