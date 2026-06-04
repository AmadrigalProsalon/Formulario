<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permiso_notificaciones')) {
            Schema::create('permiso_notificaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permiso_solicitud_id')->constrained('permisos_solicitudes')->cascadeOnDelete();
                $table->string('correo');
                $table->string('tipo');
                $table->string('estatus')->default('pendiente');
                $table->timestamp('enviado_at')->nullable();
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_notificaciones');
    }
};
