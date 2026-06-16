<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empleados')) {
            return;
        }

        Schema::table('empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('empleados', 'curp')) {
                $table->string('curp', 18)->nullable()->index()->after('numero_empleado');
            }

            if (! Schema::hasColumn('empleados', 'rfc')) {
                $table->string('rfc', 13)->nullable()->index()->after('curp');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empleados')) {
            return;
        }

        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'rfc')) {
                $table->dropColumn('rfc');
            }

            if (Schema::hasColumn('empleados', 'curp')) {
                $table->dropColumn('curp');
            }
        });
    }
};
