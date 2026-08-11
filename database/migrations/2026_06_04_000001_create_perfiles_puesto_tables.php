<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('perfiles_puesto')) {
            Schema::create('perfiles_puesto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_puesto')->index();
            $table->string('codigo')->nullable();
            $table->string('version')->nullable();
            $table->string('fecha_elaboracion')->nullable();
            $table->string('organizacion')->nullable();
            $table->string('area_departamento')->nullable()->index();
            $table->string('puesto_reporta')->nullable();
            $table->longText('descripcion_puesto')->nullable();
            $table->longText('objetivo_puesto')->nullable();
            $table->longText('requerimientos_minimos')->nullable();
            $table->longText('cualidades')->nullable();
            $table->longText('habilidades')->nullable();
            $table->longText('responsabilidades_text')->nullable();
            $table->string('archivo_original_path')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->longText('raw_text')->nullable();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('perfil_puesto_responsabilidades')) {
            Schema::create('perfil_puesto_responsabilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_puesto_id')
                ->constrained('perfiles_puesto')
                ->cascadeOnDelete();
            $table->string('titulo');
            $table->longText('descripcion')->nullable();
            $table->integer('orden')->default(1);
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_puesto_responsabilidades');
        Schema::dropIfExists('perfiles_puesto');
    }
};
