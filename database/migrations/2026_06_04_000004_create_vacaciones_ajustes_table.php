<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacaciones_ajustes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->decimal('dias', 8, 2)->default(0);
            $table->string('tipo')->default('ajuste_manual');
            $table->text('comentario')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['empleado_id', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacaciones_ajustes');
    }
};
