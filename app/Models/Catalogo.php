<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $table = 'catalogos';

    protected $fillable = [
        'tipo',
        'valor'
    ];

    public $timestamps = false; // 🔥 importante si tu tabla no tiene created_at
}
