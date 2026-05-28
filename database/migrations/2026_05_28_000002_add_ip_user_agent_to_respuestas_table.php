<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('respuestas', function (Blueprint $table) {
            if (! Schema::hasColumn('respuestas', 'ip')) {
                $table->string('ip', 45)->nullable()->after('horario');
            }

            if (! Schema::hasColumn('respuestas', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('respuestas', function (Blueprint $table) {
            if (Schema::hasColumn('respuestas', 'user_agent')) {
                $table->dropColumn('user_agent');
            }

            if (Schema::hasColumn('respuestas', 'ip')) {
                $table->dropColumn('ip');
            }
        });
    }
};
