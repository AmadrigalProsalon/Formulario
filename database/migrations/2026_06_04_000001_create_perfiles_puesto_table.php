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
                $table->longText('responsabilidades')->nullable();
                $table->text('escolaridad_detectada')->nullable();
                $table->text('experiencia_detectada')->nullable();
                $table->string('ingles_detectado')->nullable();
                $table->text('software_detectado')->nullable();
                $table->string('archivo_original_path')->nullable();
                $table->longText('texto_original')->nullable();
                $table->boolean('activo')->default(true)->index();
                $table->timestamps();
            });

            return;
        }

        Schema::table('perfiles_puesto', function (Blueprint $table) {
            $columns = Schema::getColumnListing('perfiles_puesto');

            if (! in_array('escolaridad_detectada', $columns, true)) {
                $table->text('escolaridad_detectada')->nullable()->after('responsabilidades');
            }
            if (! in_array('experiencia_detectada', $columns, true)) {
                $table->text('experiencia_detectada')->nullable()->after('escolaridad_detectada');
            }
            if (! in_array('ingles_detectado', $columns, true)) {
                $table->string('ingles_detectado')->nullable()->after('experiencia_detectada');
            }
            if (! in_array('software_detectado', $columns, true)) {
                $table->text('software_detectado')->nullable()->after('ingles_detectado');
            }
            if (! in_array('texto_original', $columns, true)) {
                $table->longText('texto_original')->nullable()->after('archivo_original_path');
            }
        });
    }

    public function down(): void
    {
        // No se borra para evitar perder perfiles importados.
    }
};
