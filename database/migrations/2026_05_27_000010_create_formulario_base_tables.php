<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('catalogos')) {
            Schema::create('catalogos', function (Blueprint $table) {
                $table->id();
                $table->string('tipo');
                $table->string('valor');
                $table->timestamps();
                $table->unique(['tipo', 'valor']);
            });
        }

        if (! Schema::hasTable('form_fields')) {
            Schema::create('form_fields', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('label');
                $table->string('type')->default('text');
                $table->boolean('required')->default(false);
                $table->boolean('visible')->default(true);
                $table->string('data_source')->nullable();
                $table->string('data_table')->nullable();
                $table->string('section')->default('general');
                $table->timestamps();
                $table->unique('name');
            });
        }

        if (! Schema::hasTable('respuestas')) {
            Schema::create('respuestas', function (Blueprint $table) {
                $table->id();
                $table->string('departamento')->nullable();
                $table->string('puesto')->nullable();
                $table->string('horario')->nullable();
                $table->json('data')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('catalogos');
    }
};
