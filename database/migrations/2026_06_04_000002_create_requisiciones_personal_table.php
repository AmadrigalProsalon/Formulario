<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisiciones_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulario_id')->nullable()->constrained('formularios')->nullOnDelete();
            $table->foreignId('respuesta_id')->nullable()->constrained('respuestas')->nullOnDelete();
            $table->foreignId('perfil_puesto_id')->nullable()->constrained('perfiles_puesto')->nullOnDelete();
            $table->string('folio')->nullable()->unique();
            $table->string('nombre_puesto')->nullable();
            $table->string('departamento')->nullable();
            $table->string('causa_vacante')->nullable();
            $table->string('tipo_contrato')->nullable();
            $table->string('estatus')->default('nueva')->index();
            $table->text('observaciones_rh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisiciones_personal');
    }
};
