<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilPuesto extends Model
{
    use HasFactory;

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
        'responsabilidades',
        'escolaridad_detectada',
        'experiencia_detectada',
        'ingles_detectado',
        'software_detectado',
        'archivo_original_path',
        'texto_original',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
