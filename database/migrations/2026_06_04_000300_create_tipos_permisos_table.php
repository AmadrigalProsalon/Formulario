<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tipos_permisos')) {
            Schema::create('tipos_permisos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('slug')->unique();
                $table->text('descripcion')->nullable();
                $table->boolean('descuenta_vacaciones')->default(false);
                $table->boolean('requiere_saldo')->default(false);
                $table->boolean('requiere_firma_colaborador')->default(true);
                $table->boolean('requiere_firma_lider')->default(true);
                $table->boolean('requiere_recepcion_rh')->default(true);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        $tipos = [
            [
                'nombre' => 'Vacaciones',
                'slug' => 'vacaciones',
                'descripcion' => 'Días de vacaciones que descuentan saldo disponible.',
                'descuenta_vacaciones' => true,
                'requiere_saldo' => true,
            ],
            [
                'nombre' => 'Permiso con goce de sueldo',
                'slug' => 'permiso-con-goce',
                'descripcion' => 'Permiso que no descuenta vacaciones y conserva sueldo.',
                'descuenta_vacaciones' => false,
                'requiere_saldo' => false,
            ],
            [
                'nombre' => 'Permiso sin goce de sueldo',
                'slug' => 'permiso-sin-goce',
                'descripcion' => 'Permiso que no descuenta vacaciones y no conserva sueldo.',
                'descuenta_vacaciones' => false,
                'requiere_saldo' => false,
            ],
            [
                'nombre' => 'Permiso médico',
                'slug' => 'permiso-medico',
                'descripcion' => 'Permiso por consulta, incapacidad o tema médico.',
                'descuenta_vacaciones' => false,
                'requiere_saldo' => false,
            ],
            [
                'nombre' => 'Otro permiso',
                'slug' => 'otro-permiso',
                'descripcion' => 'Permiso interno configurable.',
                'descuenta_vacaciones' => false,
                'requiere_saldo' => false,
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipos_permisos')->updateOrInsert(
                ['slug' => $tipo['slug']],
                array_merge($tipo, [
                    'requiere_firma_colaborador' => true,
                    'requiere_firma_lider' => true,
                    'requiere_recepcion_rh' => true,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_permisos');
    }
};
