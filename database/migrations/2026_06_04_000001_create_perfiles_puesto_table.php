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
                $table->string('slug')->nullable()->unique();

                $table->string('codigo')->nullable();
                $table->string('version')->nullable();
                $table->string('fecha_elaboracion')->nullable();

                $table->string('organizacion')->nullable();
                $table->string('area_departamento')->nullable();
                $table->string('puesto_reporta')->nullable();

                $table->longText('descripcion_puesto')->nullable();
                $table->longText('objetivo_puesto')->nullable();
                $table->longText('requerimientos_minimos')->nullable();
                $table->longText('cualidades')->nullable();
                $table->longText('habilidades')->nullable();
                $table->longText('responsabilidades')->nullable();

                $table->longText('escolaridad_detectada')->nullable();
                $table->longText('experiencia_detectada')->nullable();
                $table->longText('ingles_detectado')->nullable();
                $table->longText('software_detectado')->nullable();

                $table->string('archivo_original_path')->nullable();
                $table->boolean('activo')->default(true);

                $table->timestamps();
            });

            return;
        }

        Schema::table('perfiles_puesto', function (Blueprint $table) {
            if (! Schema::hasColumn('perfiles_puesto', 'nombre_puesto')) {
                $table->string('nombre_puesto')->nullable()->index();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'slug')) {
                $table->string('slug')->nullable()->unique();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'codigo')) {
                $table->string('codigo')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'version')) {
                $table->string('version')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'fecha_elaboracion')) {
                $table->string('fecha_elaboracion')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'organizacion')) {
                $table->string('organizacion')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'area_departamento')) {
                $table->string('area_departamento')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'puesto_reporta')) {
                $table->string('puesto_reporta')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'descripcion_puesto')) {
                $table->longText('descripcion_puesto')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'objetivo_puesto')) {
                $table->longText('objetivo_puesto')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'requerimientos_minimos')) {
                $table->longText('requerimientos_minimos')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'cualidades')) {
                $table->longText('cualidades')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'habilidades')) {
                $table->longText('habilidades')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'responsabilidades')) {
                $table->longText('responsabilidades')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'escolaridad_detectada')) {
                $table->longText('escolaridad_detectada')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'experiencia_detectada')) {
                $table->longText('experiencia_detectada')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'ingles_detectado')) {
                $table->longText('ingles_detectado')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'software_detectado')) {
                $table->longText('software_detectado')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'archivo_original_path')) {
                $table->string('archivo_original_path')->nullable();
            }

            if (! Schema::hasColumn('perfiles_puesto', 'activo')) {
                $table->boolean('activo')->default(true);
            }

            if (! Schema::hasColumn('perfiles_puesto', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_puesto');
    }
};
