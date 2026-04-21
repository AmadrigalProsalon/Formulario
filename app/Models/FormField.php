<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $table = 'form_fields';

    protected $fillable = [
        'name',
        'label',
        'type',
        'required',
        'visible',
        'data_source',
        'data_table',
        'section'
    ];

    protected $casts = [
        'required' => 'boolean',
        'visible' => 'boolean'
    ];
}
