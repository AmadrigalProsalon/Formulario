<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilPuestoRequisicionAutofillSeeder extends Seeder
{
    public function run(): void
    {
        $formularioId = DB::table('formularios')
            ->where('slug', 'requisicion-personal')
            ->value('id');

        if (! $formularioId) {
            $formularioId = DB::table('formularios')->insertGetId([
                'titulo' => 'Requisición de Personal',
                'slug' => 'requisicion-personal',
                'descripcion' => 'Formulario para solicitar una nueva vacante o reemplazo de personal.',
                'mail_to' => env('RH_FORM_MAIL_TO', 'rh@prosalon.mx'),
                'template_path' => null,
                'activo' => 1,
                'es_default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $exists = DB::table('form_fields')
            ->where('formulario_id', $formularioId)
            ->where('name', 'perfil_puesto_id')
            ->exists();

        if (! $exists) {
            DB::table('form_fields')->insert([
                'formulario_id' => $formularioId,
                'name' => 'perfil_puesto_id',
                'label' => 'Perfil de puesto base',
                'type' => 'hidden',
                'required' => 0,
                'visible' => 1,
                'data_source' => null,
                'data_table' => null,
                'section' => 'I. Perfil base',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
