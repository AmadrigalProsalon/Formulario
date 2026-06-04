<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permiso_firmas')) {
            Schema::create('permiso_firmas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permiso_solicitud_id')->constrained('permisos_solicitudes')->cascadeOnDelete();
                $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
                $table->string('tipo_firma'); // colaborador, lider, rh
                $table->string('nombre');
                $table->string('correo');
                $table->string('token', 100)->unique();
                $table->string('estatus')->default('pendiente');
                $table->string('firma_path')->nullable();
                $table->timestamp('firmado_at')->nullable();
                $table->string('ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_firmas');
    }
};
