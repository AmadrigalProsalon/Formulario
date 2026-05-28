<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('formularios')) {
            Schema::create('formularios', function (Blueprint $table) {
                $table->id();
                $table->string('titulo');
                $table->string('slug')->unique();
                $table->text('descripcion')->nullable();
                $table->string('mail_to')->nullable();
                $table->string('template_path')->nullable();
                $table->boolean('activo')->default(true);
                $table->boolean('es_default')->default(false);
                $table->timestamps();
            });
        }

        if (! DB::table('formularios')->where('es_default', true)->exists()) {
            DB::table('formularios')->insert([
                'titulo' => 'Perfil de puesto',
                'slug' => 'perfil-de-puesto',
                'descripcion' => 'Formulario principal de RH',
                'mail_to' => config('rh.mail_to', 'amadrigal@prosalon.mx'),
                'template_path' => null,
                'activo' => true,
                'es_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('formularios');
    }
};
