<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    protected $table = 'formularios';

    protected $fillable = [
        'titulo',
        'slug',
        'descripcion',
        'mail_to',
        'template_path',
        'activo',
        'es_default',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'es_default' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class, 'formulario_id');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class, 'formulario_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('es_default', true);
    }
}
