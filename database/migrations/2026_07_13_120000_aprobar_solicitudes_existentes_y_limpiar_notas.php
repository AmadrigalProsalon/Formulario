<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            return;
        }

        $updates = [
            'estatus' => 'formato_recibido',
            'formato_recibido' => 1,
            'observaciones_rh' => null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('permisos_solicitudes', 'formato_recibido_at')) {
            DB::table('permisos_solicitudes')
                ->whereNull('formato_recibido_at')
                ->update(['formato_recibido_at' => DB::raw('COALESCE(created_at, NOW())')]);
        }

        DB::table('permisos_solicitudes')->update($updates);
    }

    public function down(): void
    {
        // No se revierte porque no es posible conocer el estado anterior de cada solicitud.
    }
};
