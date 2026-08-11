<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empleados') || ! Schema::hasColumn('empleados', 'correo')) {
            return;
        }

        // Los correos pueden repetirse: varias personas comparten un correo
        // operativo y el mismo correo del jefe aparece en todos sus colaboradores.
        $indexes = DB::select("SHOW INDEX FROM empleados WHERE Column_name = 'correo' AND Non_unique = 0");

        foreach ($indexes as $index) {
            $name = $index->Key_name ?? null;

            if ($name && $name !== 'PRIMARY') {
                DB::statement('ALTER TABLE empleados DROP INDEX `' . str_replace('`', '``', $name) . '`');
            }
        }

        // Índice normal para conservar búsquedas rápidas sin exigir unicidad.
        $existing = DB::select("SHOW INDEX FROM empleados WHERE Key_name = 'empleados_correo_index'");
        if (empty($existing)) {
            DB::statement('ALTER TABLE empleados ADD INDEX empleados_correo_index (correo)');
        }
    }

    public function down(): void
    {
        // No se restaura UNIQUE porque una base ya importada puede contener
        // correos legítimamente repetidos y la reversión fallaría.
    }
};
