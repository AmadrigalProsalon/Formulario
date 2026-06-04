<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerfilPuesto extends Model
{
    protected $table = 'perfiles_puesto';

    protected $fillable = [
        'nombre_puesto',
        'codigo',
        'version',
        'fecha_elaboracion',
        'organizacion',
        'area_departamento',
        'puesto_reporta',
        'descripcion_puesto',
        'objetivo_puesto',
        'requerimientos_minimos',
        'cualidades',
        'habilidades',
        'responsabilidades_text',
        'archivo_original_path',
        'activo',
        'raw_text',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function responsabilidades(): HasMany
    {
        return $this->hasMany(PerfilPuestoResponsabilidad::class, 'perfil_puesto_id')
            ->orderBy('orden');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
