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
            if (! Schema::hasColumn('permisos_solicitudes', 'archivo_firmado_path')) {
                $table->string('archivo_firmado_path')->nullable()->after('documento_path');
            }
            if (! Schema::hasColumn('permisos_solicitudes', 'archivo_firmado_original')) {
                $table->string('archivo_firmado_original')->nullable()->after('archivo_firmado_path');
            }
            if (! Schema::hasColumn('permisos_solicitudes', 'archivo_firmado_at')) {
                $table->timestamp('archivo_firmado_at')->nullable()->after('archivo_firmado_original');
            }
            if (! Schema::hasColumn('permisos_solicitudes', 'archivo_firmado_por')) {
                $table->foreignId('archivo_firmado_por')->nullable()->after('archivo_firmado_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            return;
        }

        Schema::table('permisos_solicitudes', function (Blueprint $table) {
            foreach (['archivo_firmado_por', 'archivo_firmado_at', 'archivo_firmado_original', 'archivo_firmado_path'] as $column) {
                if (Schema::hasColumn('permisos_solicitudes', $column)) {
                    if ($column === 'archivo_firmado_por') {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
