<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empleados') && Schema::hasColumn('empleados', 'numero_empleado')) {
            DB::statement('ALTER TABLE empleados MODIFY numero_empleado VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        // No se revierte a NOT NULL porque pueden existir líderes sin número real.
    }
};
