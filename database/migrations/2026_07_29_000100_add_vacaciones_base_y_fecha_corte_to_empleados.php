<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('empleados', 'vacaciones_ganadas_base')) {
                $table->decimal('vacaciones_ganadas_base', 10, 4)->default(0)->after('vacaciones_pendientes');
            }

            if (! Schema::hasColumn('empleados', 'vacaciones_fecha_corte')) {
                $table->date('vacaciones_fecha_corte')->nullable()->after('vacaciones_ganadas_base');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'vacaciones_fecha_corte')) {
                $table->dropColumn('vacaciones_fecha_corte');
            }

            if (Schema::hasColumn('empleados', 'vacaciones_ganadas_base')) {
                $table->dropColumn('vacaciones_ganadas_base');
            }
        });
    }
};
