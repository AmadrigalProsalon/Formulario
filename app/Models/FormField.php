<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $table = 'form_fields';

    protected $fillable = [
        'formulario_id',
        'name',
        'label',
        'type',
        'required',
        'visible',
        'data_source',
        'data_table',
        'section',
    ];

    protected $casts = [
        'required' => 'boolean',
        'visible' => 'boolean',
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }
}
