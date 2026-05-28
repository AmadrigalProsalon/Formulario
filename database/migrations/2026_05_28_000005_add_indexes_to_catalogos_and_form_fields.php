<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('CREATE INDEX catalogos_tipo_index ON catalogos (tipo)');
        } catch (Throwable $e) {
            //
        }

        try {
            DB::statement('CREATE UNIQUE INDEX catalogos_tipo_valor_unique ON catalogos (tipo, valor)');
        } catch (Throwable $e) {
            //
        }

        try {
            DB::statement('CREATE UNIQUE INDEX form_fields_formulario_name_unique ON form_fields (formulario_id, name)');
        } catch (Throwable $e) {
            //
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP INDEX catalogos_tipo_index ON catalogos');
        } catch (Throwable $e) {
            //
        }

        try {
            DB::statement('DROP INDEX catalogos_tipo_valor_unique ON catalogos');
        } catch (Throwable $e) {
            //
        }

        try {
            DB::statement('DROP INDEX form_fields_formulario_name_unique ON form_fields');
        } catch (Throwable $e) {
            //
        }
    }
};
