<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            return;
        }

        Schema::table('permisos_solicitudes', function (Blueprint $table) {
            if (! Schema::hasColumn('permisos_solicitudes', 'documento_path')) {
                $table->string('documento_path')->nullable()->after('motivo');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'documento_enviado_at')) {
                $table->timestamp('documento_enviado_at')->nullable()->after('documento_path');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'formato_recibido')) {
                $table->boolean('formato_recibido')->default(false)->after('estatus');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'formato_recibido_at')) {
                $table->timestamp('formato_recibido_at')->nullable()->after('formato_recibido');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'formato_recibido_por')) {
                $table->foreignId('formato_recibido_por')->nullable()->after('formato_recibido_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'observaciones_rh')) {
                $table->text('observaciones_rh')->nullable()->after('formato_recibido_por');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'cancelado_at')) {
                $table->timestamp('cancelado_at')->nullable()->after('observaciones_rh');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'cancelado_por')) {
                $table->foreignId('cancelado_por')->nullable()->after('cancelado_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            return;
        }

        Schema::table('permisos_solicitudes', function (Blueprint $table) {
            foreach ([
                'cancelado_por',
                'formato_recibido_por',
            ] as $column) {
                if (Schema::hasColumn('permisos_solicitudes', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach ([
                'documento_path',
                'documento_enviado_at',
                'formato_recibido',
                'formato_recibido_at',
                'observaciones_rh',
                'cancelado_at',
            ] as $column) {
                if (Schema::hasColumn('permisos_solicitudes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
