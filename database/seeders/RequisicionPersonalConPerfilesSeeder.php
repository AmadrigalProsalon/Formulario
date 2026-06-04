<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequisicionPersonalConPerfilesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('formularios')->updateOrInsert(
            ['slug' => 'requisicion-personal'],
            [
                'titulo' => 'Requisición de Personal',
                'descripcion' => 'Formulario para solicitar una nueva vacante o reemplazo de personal. Puede autollenarse desde perfiles de puesto importados desde Word.',
                'mail_to' => env('RH_FORM_MAIL_TO', 'rh@prosalon.mx'),
                'template_path' => null,
                'activo' => true,
                'es_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $formularioId = DB::table('formularios')->where('slug', 'requisicion-personal')->value('id');

        $this->catalogos();

        DB::table('form_fields')->where('formulario_id', $formularioId)->delete();

        $fields = [
            ['perfil_puesto_id', 'Perfil de puesto base', 'text', 0, 1, null, null, 'III. Datos del puesto'],
            ['departamento_solicitante', 'Departamento', 'select', 1, 1, 'db', 'departamento', 'I. Departamento solicitante'],
            ['causa_vacante', 'Causa de la vacante', 'radio', 1, 1, 'db', 'causa_vacante', 'II. Vacante y contrato'],
            ['tipo_contrato', 'Tipo de contrato', 'radio', 1, 1, 'db', 'tipo_contrato', 'II. Vacante y contrato'],
            ['nombre_puesto', 'Nombre del puesto', 'select', 1, 1, 'db', 'puesto_requisicion', 'III. Datos del puesto'],
            ['area_departamento_puesto', 'Área o departamento del puesto', 'select', 1, 1, 'db', 'departamento', 'III. Datos del puesto'],
            ['ubicacion_fisica_puesto', 'Ubicación física del puesto', 'select', 1, 1, 'db', 'ubicacion_puesto', 'III. Datos del puesto'],
            ['horario_jornada_laboral', 'Horario de jornada laboral', 'select', 1, 1, 'db', 'horario_laboral', 'III. Datos del puesto'],
            ['puesto_a_quien_reporta', 'Puesto a quien reporta', 'select', 1, 1, 'db', 'puesto_reporta', 'III. Datos del puesto'],
            ['software_requerido', 'Requerimientos de licencias o software', 'checkbox', 0, 1, 'db', 'software_requerido', 'III. Datos del puesto'],
            ['hardware_requerido', 'Requerimientos de hardware y/o equipos', 'checkbox', 0, 1, 'db', 'hardware_requerido', 'III. Datos del puesto'],
            ['requiere_correo_electronico', 'Requiere correo electrónico', 'radio', 1, 1, 'db', 'si_no', 'III. Datos del puesto'],
            ['funciones_generales_puesto', 'Funciones generales del puesto', 'textarea', 1, 1, null, null, 'III. Datos del puesto'],
            ['escolaridad', 'Escolaridad o grado académico', 'select', 1, 1, 'db', 'escolaridad', 'IV. Perfil requerido'],
            ['rango_edad', 'Rango de edad', 'text', 1, 1, null, null, 'IV. Perfil requerido'],
            ['sexo', 'Sexo', 'radio', 1, 1, 'db', 'sexo_requisicion', 'IV. Perfil requerido'],
            ['disponibilidad_viajar', 'Disponibilidad para viajar', 'radio', 1, 1, 'db', 'si_no', 'IV. Perfil requerido'],
            ['area_experiencia', 'Área de experiencia', 'textarea', 1, 1, null, null, 'IV. Perfil requerido'],
            ['anios_experiencia', 'Años de experiencia', 'radio', 1, 1, 'db', 'anios_experiencia', 'IV. Perfil requerido'],
            ['conocimientos_indispensables', 'Conocimientos específicos indispensables', 'textarea', 1, 1, null, null, 'IV. Perfil requerido'],
            ['conocimientos_deseables', 'Conocimientos específicos deseables', 'textarea', 1, 1, null, null, 'IV. Perfil requerido'],
            ['habilidades_indispensables', 'Habilidades indispensables', 'textarea', 1, 1, null, null, 'IV. Perfil requerido'],
            ['habilidades_deseables', 'Habilidades deseables', 'textarea', 0, 1, null, null, 'IV. Perfil requerido'],
            ['software_especifico', 'Habilidades y/o conocimientos específicos de software', 'textarea', 1, 1, null, null, 'IV. Perfil requerido'],
            ['hardware_especifico', 'Habilidades y/o conocimientos de hardware requeridos', 'textarea', 1, 1, null, null, 'IV. Perfil requerido'],
            ['nivel_ingles', 'Nivel de inglés requerido', 'radio', 1, 1, 'db', 'nivel_ingles', 'IV. Perfil requerido'],
            ['notas_observaciones', 'Notas u observaciones', 'textarea', 0, 1, null, null, 'IV. Perfil requerido'],
        ];

        foreach ($fields as $field) {
            DB::table('form_fields')->insert([
                'formulario_id' => $formularioId,
                'name' => $field[0],
                'label' => $field[1],
                'type' => $field[2],
                'required' => $field[3],
                'visible' => $field[4],
                'data_source' => $field[5],
                'data_table' => $field[6],
                'section' => $field[7],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function catalogos(): void
    {
        $this->insertCatalogo('departamento', [
            'Administración', 'Almacén', 'Asuntos Legales', 'Aseguramiento de Calidad', 'Compras', 'Contabilidad',
            'Laboratorio', 'Logística', 'Mantenimiento', 'Marketing', 'Mercadotecnia', 'Operaciones', 'Producción',
            'Recursos Humanos', 'Responsabilidad Social', 'Seguridad e Higiene', 'Sistemas / TI', 'Telemarketing', 'Ventas',
            'ATENCIÓN AL CLIENTE',
        ]);

        $this->insertCatalogo('causa_vacante', ['Reemplazo puesto vacante', 'Puesto de nueva creación']);
        $this->insertCatalogo('tipo_contrato', ['Temporal', 'Planta', 'Por proyecto']);
        $this->insertCatalogo('ubicacion_puesto', ['Oficinas', 'Acatlán', 'Viñedo', 'Punta Mita', 'Otra']);
        $this->insertCatalogo('horario_laboral', [
            'Lunes a viernes de 8:00 a 18:00',
            'Lunes a Jueves de 8:30 a 18:00 y viernes de 8:30 a 14:30',
            'Lunes a sábado de 6:00 a 13:30',
            'Domingo a viernes de 14:00 a 21:30',
            'Lunes a viernes de 8:30 a 17:00 y sábado de 8:30 a 13:00',
            'Lunes a viernes 8:30 a 18:30',
            'Otro',
        ]);
        $this->insertCatalogo('puesto_reporta', [
            'ADMINISTRADOR DE SERVICIOS GENERALES', 'JEFE DE OPERACIONES', 'GERENTE GENERAL', 'GERENTE COMERCIAL',
            'DIRECTOR ADMINISTRATIVO', 'GERENTE DE MERCADOTECNIA', 'JEFE DE MANUFACTURA', 'JEFE DE MANTENIMIENTO',
            'JEFE DE ASEGURAMIENTO DE CALIDAD', 'JEFE DE CONTABILIDAD', 'JEFE DE COMPRAS', 'JEFE DE ASUNTOS LEGALES',
            'JEFE DE RESPONSABILIDAD SOCIAL', 'DESARROLLADOR DE NEGOCIOS', 'DIRECTOR DE OPERACIONES', 'JEFE DE ALMACEN',
            'JEFE DE TELEMARKETING', 'GERENTE DE EXPANSION COMERCIAL', 'GERENTE DE LABORATORIO', 'Gerente de Marketing', 'Otro',
        ]);
        $this->insertCatalogo('software_requerido', ['Office', 'Netsuite', 'RH Flex', 'Power BI', 'Desk', 'Otras']);
        $this->insertCatalogo('hardware_requerido', ['Computadora de escritorio', 'Laptop', 'Celular', 'Otras']);
        $this->insertCatalogo('escolaridad', ['Primaria', 'Secundaria', 'Preparatoria / Bachillerato', 'Carrera técnica', 'Licenciatura', 'Ingeniería', 'Maestría', 'Doctorado', 'Indistinto']);
        $this->insertCatalogo('sexo_requisicion', ['Hombre', 'Mujer', 'Indistinto']);
        $this->insertCatalogo('si_no', ['Si', 'No']);
        $this->insertCatalogo('anios_experiencia', ['0 a 1 año', '1 a 2 años', '3 a 5 años', 'Otras']);
        $this->insertCatalogo('nivel_ingles', ['Ninguno', 'Básico', 'Intermedio', 'Avanzado']);
        $this->insertCatalogo('puesto_requisicion', $this->puestos());
    }

    private function insertCatalogo(string $tipo, array $valores): void
    {
        foreach (array_unique($valores) as $valor) {
            DB::table('catalogos')->updateOrInsert([
                'tipo' => $tipo,
                'valor' => $valor,
            ], []);
        }
    }

    private function puestos(): array
    {
        return [
            'ADMINISTRADOR DE SERVICIOS GENERALES','ANALISTA DE ADMINISTRACION DE PERSONAL','ANALISTA DE ASUNTOS LEGALES',
            'ANALISTA DE ATRACCIÓN DE TALENTO','ANALISTA DE DATOS','ANALISTA DE LABORATORIO','ANALISTA DE LOGISTICA',
            'ANALISTA DE PRODUCCIÓN','ASISTENTE DE TELEMARKETING','ASISTENTE PRINCIPAL DE COCINA','AUXILIAR DE COCINA',
            'AUXILIAR DE LIMPIEZA','AUXILIAR DE MANTENIMIENTO','AUXILIAR DE MANTENIMIENTO Y JARDINERIA',
            'AUXILIAR DE PRODUCCIÓN','AUXILIAR DE PRODUCCION DE PLASTICOS','AUXILIAR DE RECURSOS HUMANOS',
            'AUXILIAR DE SERVICIOS GENERALES','AUXILIAR OPERATIVO DE RESPONSABILIDAD SOCIAL','CAMARISTA','CHOFER',
            'COMMUNITY MANAGER','COMPRADOR JR','CONTADOR JR','CONTADOR SR','COORDINADOR DE EVENTOS Y RELACIONES PUBLICAS',
            'COORDINADOR DE MARKETING DIGITAL','COORDINADOR DE SEGURIDAD E HIGIENE','DESARROLLADOR AUDIOVISUAL',
            'DESARROLLADOR WEB','DIRECTOR DE OPERACIONES','DISEÑADOR GRAFICO JR','DISEÑADOR GRAFICO SR','DISEÑADOR INDUSTRIAL',
            'DISEÑADOR WEB','EDUCADOR JR','EDUCADOR SR','EJECUTIVA DE ATENCION A CLIENTE','EJECUTIVO COMERCIAL JR',
            'EJECUTIVO COMERCIAL SR','EJECUTIVO DE ATENCION A CLIENTES','EJECUTIVO DE TELEMARKETING',
            'ENCARGADO DE MANTENIMIENTO Y JARDINERIA','FORMULADOR JR','GENERALISTA DE RECURSOS HUMANOS','GERENTE COMERCIAL',
            'GERENTE DE ALMACEN Y LOGISTICA','GERENTE DE EXPANSION COMERCIAL','GERENTE DE LABORATORIO','GERENTE DE MERCADOTECNIA',
            'GERENTE GENERAL','INGENIERO DE SOPORTE TÉCNICO TI','INSPECTOR DE CALIDAD','INTENDENTE','JARDINERO',
            'JEFE DE ALMACEN','JEFE DE ASEGURAMIENTO DE CALIDAD','JEFE DE ASUNTOS LEGALES','JEFE DE COMPRAS',
            'JEFE DE CONTABILIDAD','JEFE DE LABORATORIO','JEFE DE MANUFACTURA','JEFE DE MANTENIMIENTO','JEFE DE OPERACIONES',
            'JEFE DE RESPONSABILIDAD SOCIAL','JEFE DE TELEMARKETING','MANTENIMIENTO Y JARDINERIA','MESERO','OPERADOR DE PRODUCCION',
            'OPERADOR TECNICO DE MANUFACTURA','PESADOR DE MATERIAS PRIMAS','RECEPCIONISTA','SUPERVISOR DE ALMACEN T/M',
            'SUPERVISOR DE EMPAQUE','SUPERVISOR DE MANUFACTURA','TECNICO DE OBRA E INGENIERIA','TECNICO ELECTRICO',
            'TECNICO MECANICO','VETERINARIO','VIGILANTE','Atención al Cliente','Otro'
        ];
    }
}
