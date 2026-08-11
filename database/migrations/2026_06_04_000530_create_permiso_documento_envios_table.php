<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permiso_documento_envios')) {
            return;
        }

        Schema::create('permiso_documento_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permiso_solicitud_id')->constrained('permisos_solicitudes')->cascadeOnDelete();
            $table->string('correo');
            $table->string('tipo')->default('documento_inicial');
            $table->string('estatus')->default('enviado');
            $table->text('error')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_documento_envios');
    }
};
