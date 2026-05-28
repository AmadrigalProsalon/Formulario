<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultFormId = DB::table('formularios')
            ->where('es_default', true)
            ->value('id');

        if (! $defaultFormId) {
            $defaultFormId = DB::table('formularios')->insertGetId([
                'titulo' => 'Perfil de puesto',
                'slug' => 'perfil-de-puesto',
                'descripcion' => 'Formulario principal de RH',
                'activo' => true,
                'es_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasColumn('form_fields', 'formulario_id')) {
            Schema::table('form_fields', function (Blueprint $table) {
                $table->foreignId('formulario_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('formularios')
                    ->nullOnDelete();
            });

            DB::table('form_fields')
                ->whereNull('formulario_id')
                ->update(['formulario_id' => $defaultFormId]);
        }

        if (! Schema::hasColumn('respuestas', 'formulario_id')) {
            Schema::table('respuestas', function (Blueprint $table) {
                $table->foreignId('formulario_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('formularios')
                    ->nullOnDelete();
            });

            DB::table('respuestas')
                ->whereNull('formulario_id')
                ->update(['formulario_id' => $defaultFormId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('form_fields', 'formulario_id')) {
            Schema::table('form_fields', function (Blueprint $table) {
                $table->dropConstrainedForeignId('formulario_id');
            });
        }

        if (Schema::hasColumn('respuestas', 'formulario_id')) {
            Schema::table('respuestas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('formulario_id');
            });
        }
    }
};
