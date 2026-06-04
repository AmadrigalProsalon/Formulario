<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empleados')) {
            Schema::create('empleados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->unsignedBigInteger('lider_id')->nullable()->index();
                $table->string('numero_empleado')->nullable()->unique();
                $table->string('nombre');
                $table->string('correo')->unique();
                $table->string('puesto')->nullable();
                $table->date('fecha_ingreso')->nullable();
                $table->boolean('es_lider')->default(false);
                $table->boolean('activo')->default(true);
                $table->decimal('vacaciones_ajuste', 8, 2)->default(0);
                $table->decimal('vacaciones_usados', 8, 2)->default(0);
                $table->decimal('vacaciones_pendientes', 8, 2)->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('empleados', function (Blueprint $table) {
                if (! Schema::hasColumn('empleados', 'area_id')) {
                    $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete()->after('id');
                }
                if (! Schema::hasColumn('empleados', 'lider_id')) {
                    $table->unsignedBigInteger('lider_id')->nullable()->index()->after('area_id');
                }
                if (! Schema::hasColumn('empleados', 'numero_empleado')) {
                    $table->string('numero_empleado')->nullable()->unique()->after('lider_id');
                }
                if (! Schema::hasColumn('empleados', 'fecha_ingreso')) {
                    $table->date('fecha_ingreso')->nullable()->after('puesto');
                }
                if (! Schema::hasColumn('empleados', 'es_lider')) {
                    $table->boolean('es_lider')->default(false)->after('fecha_ingreso');
                }
                if (! Schema::hasColumn('empleados', 'activo')) {
                    $table->boolean('activo')->default(true)->after('es_lider');
                }
                if (! Schema::hasColumn('empleados', 'vacaciones_ajuste')) {
                    $table->decimal('vacaciones_ajuste', 8, 2)->default(0)->after('activo');
                }
                if (! Schema::hasColumn('empleados', 'vacaciones_usados')) {
                    $table->decimal('vacaciones_usados', 8, 2)->default(0)->after('vacaciones_ajuste');
                }
                if (! Schema::hasColumn('empleados', 'vacaciones_pendientes')) {
                    $table->decimal('vacaciones_pendientes', 8, 2)->default(0)->after('vacaciones_usados');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
