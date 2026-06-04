<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dias_inhabiles')) {
            Schema::create('dias_inhabiles', function (Blueprint $table) {
                $table->id();
                $table->date('fecha')->unique();
                $table->string('nombre');
                $table->string('tipo')->default('oficial');
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dias_inhabiles');
    }
};
