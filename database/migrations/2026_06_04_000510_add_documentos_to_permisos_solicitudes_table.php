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
            if (! Schema::hasColumn('permisos_solicitudes', 'documento_inicial_path')) {
                $table->string('documento_inicial_path')->nullable()->after('observaciones_rh');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'documento_firmado_path')) {
                $table->string('documento_firmado_path')->nullable()->after('documento_inicial_path');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'documento_inicial_enviado_at')) {
                $table->timestamp('documento_inicial_enviado_at')->nullable()->after('documento_firmado_path');
            }

            if (! Schema::hasColumn('permisos_solicitudes', 'documento_firmado_enviado_rh_at')) {
                $table->timestamp('documento_firmado_enviado_rh_at')->nullable()->after('documento_inicial_enviado_at');
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
                'documento_firmado_enviado_rh_at',
                'documento_inicial_enviado_at',
                'documento_firmado_path',
                'documento_inicial_path',
            ] as $column) {
                if (Schema::hasColumn('permisos_solicitudes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
