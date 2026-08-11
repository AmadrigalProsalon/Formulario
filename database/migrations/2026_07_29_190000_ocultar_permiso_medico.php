<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tipos_permisos')) {
            return;
        }

        DB::table('tipos_permisos')
            ->where('slug', 'permiso-medico')
            ->update([
                'activo' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('tipos_permisos')) {
            return;
        }

        DB::table('tipos_permisos')
            ->where('slug', 'permiso-medico')
            ->update([
                'activo' => true,
                'updated_at' => now(),
            ]);
    }
};
