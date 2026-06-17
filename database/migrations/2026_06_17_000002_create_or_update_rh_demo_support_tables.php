<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('areas', function (Blueprint $table) {
                $this->add($table, 'nombre', 'string', ['nullable' => true]);
                $this->add($table, 'descripcion', 'text', ['nullable' => true]);
                $this->add($table, 'activo', 'boolean', ['default' => true]);
                if (! Schema::hasColumn('areas', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        if (! Schema::hasTable('empleados')) {
            Schema::create('empleados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->foreignId('lider_id')->nullable()->constrained('empleados')->nullOnDelete();
                $table->string('numero_empleado')->nullable()->index();
                $table->string('nombre')->index();
                $table->string('correo')->nullable()->index();
                $table->string('curp')->nullable()->index();
                $table->string('rfc')->nullable()->index();
                $table->string('puesto')->nullable();
                $table->date('fecha_ingreso')->nullable();
                $table->boolean('es_lider')->default(false);
                $table->decimal('dias_vacaciones', 8, 2)->default(0);
                $table->decimal('dias_vacaciones_usados', 8, 2)->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('empleados', function (Blueprint $table) {
                $this->add($table, 'area_id', 'unsignedBigInteger', ['nullable' => true]);
                $this->add($table, 'lider_id', 'unsignedBigInteger', ['nullable' => true]);
                $this->add($table, 'numero_empleado', 'string', ['nullable' => true]);
                $this->add($table, 'nombre', 'string', ['nullable' => true]);
                $this->add($table, 'correo', 'string', ['nullable' => true]);
                $this->add($table, 'curp', 'string', ['nullable' => true]);
                $this->add($table, 'rfc', 'string', ['nullable' => true]);
                $this->add($table, 'puesto', 'string', ['nullable' => true]);
                $this->add($table, 'fecha_ingreso', 'date', ['nullable' => true]);
                $this->add($table, 'es_lider', 'boolean', ['default' => false]);
                $this->add($table, 'dias_vacaciones', 'decimal', ['default' => 0]);
                $this->add($table, 'dias_vacaciones_usados', 'decimal', ['default' => 0]);
                $this->add($table, 'activo', 'boolean', ['default' => true]);
                if (! Schema::hasColumn('empleados', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        if (! Schema::hasTable('tipos_permisos')) {
            Schema::create('tipos_permisos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('slug')->unique();
                $table->boolean('descuenta_vacaciones')->default(false);
                $table->boolean('requiere_saldo')->default(false);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permisos_solicitudes')) {
            Schema::create('permisos_solicitudes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tipo_permiso_id')->nullable()->constrained('tipos_permisos')->nullOnDelete();
                $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->foreignId('lider_id')->nullable()->constrained('empleados')->nullOnDelete();
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->decimal('dias_solicitados', 8, 2)->default(0);
                $table->text('motivo')->nullable();
                $table->string('estatus')->default('formato_pendiente');
                $table->boolean('formato_recibido')->default(false);
                $table->timestamp('formato_recibido_at')->nullable();
                $table->text('observaciones_rh')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // No eliminamos tablas operativas.
    }

    private function add(Blueprint $table, string $column, string $type, array $options = []): void
    {
        $tableName = $table->getTable();
        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        if ($type === 'decimal') {
            $definition = $table->decimal($column, 8, 2);
        } else {
            $definition = $table->{$type}($column);
        }

        if (($options['nullable'] ?? false) === true) {
            $definition->nullable();
        }

        if (array_key_exists('default', $options)) {
            $definition->default($options['default']);
        }
    }
};
