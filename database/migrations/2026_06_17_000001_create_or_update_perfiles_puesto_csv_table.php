<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('perfiles_puesto')) {
            Schema::create('perfiles_puesto', function (Blueprint $table) {
                $table->id();
                $table->string('unique_key')->nullable()->index();
                $table->string('codigo')->nullable();
                $table->string('nombre_puesto')->nullable()->index();
                $table->string('slug')->nullable()->index();
                $table->string('version')->nullable();
                $table->string('fecha_elaboracion')->nullable();
                $table->string('organizacion')->nullable();
                $table->string('area_departamento')->nullable()->index();
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
                $table->longText('hardware_detectado')->nullable();
                $table->string('archivo_original_path')->nullable();
                $table->longText('texto_original')->nullable();
                $table->json('datos_extra')->nullable();
                $table->boolean('activo')->default(true)->index();
                $table->timestamp('importado_at')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('perfiles_puesto', function (Blueprint $table) {
                $this->addColumnIfMissing($table, 'unique_key', 'string', ['nullable' => true, 'index' => true]);
                $this->addColumnIfMissing($table, 'codigo', 'string', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'nombre_puesto', 'string', ['nullable' => true, 'index' => true]);
                $this->addColumnIfMissing($table, 'slug', 'string', ['nullable' => true, 'index' => true]);
                $this->addColumnIfMissing($table, 'version', 'string', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'fecha_elaboracion', 'string', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'organizacion', 'string', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'area_departamento', 'string', ['nullable' => true, 'index' => true]);
                $this->addColumnIfMissing($table, 'puesto_reporta', 'string', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'descripcion_puesto', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'objetivo_puesto', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'requerimientos_minimos', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'cualidades', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'habilidades', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'responsabilidades', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'escolaridad_detectada', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'experiencia_detectada', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'ingles_detectado', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'software_detectado', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'hardware_detectado', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'archivo_original_path', 'string', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'texto_original', 'longText', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'datos_extra', 'json', ['nullable' => true]);
                $this->addColumnIfMissing($table, 'activo', 'boolean', ['default' => true, 'index' => true]);
                $this->addColumnIfMissing($table, 'importado_at', 'timestamp', ['nullable' => true]);

                if (! Schema::hasColumn('perfiles_puesto', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        $this->ensureUniqueIndex();
    }

    public function down(): void
    {
        // No eliminamos la tabla para no perder información importada por RH.
    }

    private function addColumnIfMissing(Blueprint $table, string $column, string $type, array $options = []): void
    {
        if (Schema::hasColumn('perfiles_puesto', $column)) {
            return;
        }

        $definition = $table->{$type}($column);

        if (($options['nullable'] ?? false) === true) {
            $definition->nullable();
        }

        if (array_key_exists('default', $options)) {
            $definition->default($options['default']);
        }

        if (($options['index'] ?? false) === true) {
            $definition->index();
        }
    }

    private function ensureUniqueIndex(): void
    {
        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'perfiles_puesto')
            ->where('index_name', 'perfiles_puesto_unique_key_unique')
            ->exists();

        if (! $exists && Schema::hasColumn('perfiles_puesto', 'unique_key')) {
            try {
                DB::statement('ALTER TABLE perfiles_puesto ADD UNIQUE INDEX perfiles_puesto_unique_key_unique (unique_key)');
            } catch (Throwable $e) {
                // Si hay datos duplicados previos, no detenemos la migración.
            }
        }
    }
};
