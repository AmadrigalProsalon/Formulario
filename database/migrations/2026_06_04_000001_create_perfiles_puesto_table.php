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

                $table->string('nombre_puesto')->nullable()->index();
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

                $table->longText('escolaridad_detectada')->nullable();
                $table->longText('experiencia_detectada')->nullable();
                $table->string('ingles_detectado')->nullable();
                $table->longText('software_detectado')->nullable();

                $table->string('archivo_original_path')->nullable();
                $table->longText('texto_original')->nullable();
                $table->boolean('activo')->default(true)->index();

                $table->timestamps();
            });

            return;
        }

        $columns = Schema::getColumnListing('perfiles_puesto');

        Schema::table('perfiles_puesto', function (Blueprint $table) use ($columns) {
            if (! in_array('nombre_puesto', $columns, true)) {
                $table->string('nombre_puesto')->nullable()->index();
            }

            if (! in_array('codigo', $columns, true)) {
                $table->string('codigo')->nullable();
            }

            if (! in_array('version', $columns, true)) {
                $table->string('version')->nullable();
            }

            if (! in_array('fecha_elaboracion', $columns, true)) {
                $table->string('fecha_elaboracion')->nullable();
            }

            if (! in_array('organizacion', $columns, true)) {
                $table->string('organizacion')->nullable();
            }

            if (! in_array('area_departamento', $columns, true)) {
                $table->string('area_departamento')->nullable()->index();
            }

            if (! in_array('puesto_reporta', $columns, true)) {
                $table->string('puesto_reporta')->nullable();
            }

            if (! in_array('descripcion_puesto', $columns, true)) {
                $table->longText('descripcion_puesto')->nullable();
            }

            if (! in_array('objetivo_puesto', $columns, true)) {
                $table->longText('objetivo_puesto')->nullable();
            }

            if (! in_array('requerimientos_minimos', $columns, true)) {
                $table->longText('requerimientos_minimos')->nullable();
            }

            if (! in_array('cualidades', $columns, true)) {
                $table->longText('cualidades')->nullable();
            }

            if (! in_array('habilidades', $columns, true)) {
                $table->longText('habilidades')->nullable();
            }

            if (! in_array('responsabilidades', $columns, true)) {
                $table->longText('responsabilidades')->nullable();
            }

            if (! in_array('escolaridad_detectada', $columns, true)) {
                $table->longText('escolaridad_detectada')->nullable();
            }

            if (! in_array('experiencia_detectada', $columns, true)) {
                $table->longText('experiencia_detectada')->nullable();
            }

            if (! in_array('ingles_detectado', $columns, true)) {
                $table->string('ingles_detectado')->nullable();
            }

            if (! in_array('software_detectado', $columns, true)) {
                $table->longText('software_detectado')->nullable();
            }

            if (! in_array('archivo_original_path', $columns, true)) {
                $table->string('archivo_original_path')->nullable();
            }

            if (! in_array('texto_original', $columns, true)) {
                $table->longText('texto_original')->nullable();
            }

            if (! in_array('activo', $columns, true)) {
                $table->boolean('activo')->default(true)->index();
            }

            if (! in_array('created_at', $columns, true) && ! in_array('updated_at', $columns, true)) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // No se borra la tabla para evitar perder perfiles importados.
    }
};
