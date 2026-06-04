<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            Schema::create('permisos_solicitudes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tipo_permiso_id')->constrained('tipos_permisos')->cascadeOnDelete();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->unsignedBigInteger('lider_id')->nullable()->index();
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                $table->decimal('dias_solicitados', 8, 2)->default(0);
                $table->text('motivo')->nullable();
                $table->string('estatus')->default('pendiente_firma_colaborador');
                $table->boolean('formato_recibido')->default(false);
                $table->timestamp('formato_recibido_at')->nullable();
                $table->foreignId('formato_recibido_por')->nullable()->constrained('users')->nullOnDelete();
                $table->text('observaciones_rh')->nullable();
                $table->string('ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_solicitudes');
    }
};
