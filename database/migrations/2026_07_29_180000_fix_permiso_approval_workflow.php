<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            return;
        }

        Schema::table('permisos_solicitudes', function (Blueprint $table) {
            if (! Schema::hasColumn('permisos_solicitudes', 'rechazado_at')) {
                $table->timestamp('rechazado_at')->nullable()->after('cancelado_por');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'rechazado_por')) {
                $table->foreignId('rechazado_por')
                    ->nullable()
                    ->after('rechazado_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        // Los movimientos importados desde el histórico no son solicitudes
        // aprobadas por RH: se identifican expresamente como registros históricos.
        DB::table('permisos_solicitudes')
            ->where(function ($query) {
                $query->where('motivo', 'like', '%histórica importada%')
                    ->orWhere('motivo', 'like', '%historica importada%');
            })
            ->update([
                'estatus' => 'historico',
                'formato_recibido' => 1,
                'updated_at' => now(),
            ]);

        // Una solicitud solo se considera aprobada cuando RH cargó el formato
        // firmado. Esto corrige los registros que una migración anterior marcó
        // automáticamente como recibidos.
        if (Schema::hasColumn('permisos_solicitudes', 'archivo_firmado_path')) {
            DB::table('permisos_solicitudes')
                ->where('estatus', 'formato_recibido')
                ->where(function ($query) {
                    $query->whereNull('archivo_firmado_path')
                        ->orWhere('archivo_firmado_path', '');
                })
                ->where(function ($query) {
                    $query->whereNull('motivo')
                        ->orWhere(function ($subquery) {
                            $subquery->where('motivo', 'not like', '%histórica importada%')
                                ->where('motivo', 'not like', '%historica importada%');
                        });
                })
                ->update([
                    'estatus' => 'formato_pendiente',
                    'formato_recibido' => 0,
                    'formato_recibido_at' => null,
                    'formato_recibido_por' => null,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // No revertimos estados para evitar volver a aprobar solicitudes sin formato.
    }
};
