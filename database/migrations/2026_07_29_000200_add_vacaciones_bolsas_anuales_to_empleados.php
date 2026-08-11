<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('empleados', 'vacaciones_saldo_anterior_base')) {
                $table->decimal('vacaciones_saldo_anterior_base', 10, 4)->default(0)->after('vacaciones_ganadas_base');
            }
            if (! Schema::hasColumn('empleados', 'vacaciones_saldo_actual_base')) {
                $table->decimal('vacaciones_saldo_actual_base', 10, 4)->default(0)->after('vacaciones_saldo_anterior_base');
            }
            if (! Schema::hasColumn('empleados', 'vacaciones_anio_base')) {
                $table->unsignedSmallInteger('vacaciones_anio_base')->nullable()->after('vacaciones_saldo_actual_base');
            }
            if (! Schema::hasColumn('empleados', 'vacaciones_fecha_vencimiento')) {
                $table->date('vacaciones_fecha_vencimiento')->nullable()->after('vacaciones_anio_base');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            foreach ([
                'vacaciones_saldo_anterior_base',
                'vacaciones_saldo_actual_base',
                'vacaciones_anio_base',
                'vacaciones_fecha_vencimiento',
            ] as $column) {
                if (Schema::hasColumn('empleados', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
