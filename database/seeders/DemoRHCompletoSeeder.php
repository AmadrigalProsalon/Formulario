<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoRHCompletoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAreasEmpleadosYVacaciones();
        $this->seedTiposPermisos();
        $this->seedPerfilesPuestoDemo();
        $this->seedCatalogosBase();
        $this->seedFormulariosDemo();
        $this->seedSolicitudesPermisosDemo();
    }

    private function seedAreasEmpleadosYVacaciones(): void
    {
        if (! Schema::hasTable('areas') || ! Schema::hasTable('empleados')) {
            return;
        }

        $areas = ['Recursos Humanos', 'Sistemas / TI', 'Ventas', 'Almacén', 'Marketing', 'Atención al Cliente'];

        foreach ($areas as $area) {
            DB::table('areas')->updateOrInsert(
                ['nombre' => $area],
                $this->withTimestamps(['descripcion' => 'Área demo: ' . $area, 'activo' => 1], 'areas')
            );
        }

        $areaIds = DB::table('areas')->pluck('id', 'nombre');

        $lideres = [
            ['E9001', 'Laura Martínez RH', 'laura.rh@prosalon.mx', 'Recursos Humanos', 'Gerente de Recursos Humanos'],
            ['E9002', 'Aldo Gómez TI', 'aldo.ti@prosalon.mx', 'Sistemas / TI', 'Jefe de Sistemas'],
            ['E9003', 'Karla Ruiz Ventas', 'karla.ventas@prosalon.mx', 'Ventas', 'Gerente Comercial'],
            ['E9004', 'Roberto Díaz Almacén', 'roberto.almacen@prosalon.mx', 'Almacén', 'Jefe de Almacén'],
            ['E9005', 'Mariana Torres Marketing', 'mariana.marketing@prosalon.mx', 'Marketing', 'Gerente de Mercadotecnia'],
            ['E9006', 'Paola Reyes Atención', 'paola.atencion@prosalon.mx', 'Atención al Cliente', 'Coordinadora de Atención al Cliente'],
        ];

        foreach ($lideres as [$numero, $nombre, $correo, $area, $puesto]) {
            DB::table('empleados')->updateOrInsert(
                ['numero_empleado' => $numero],
                $this->withTimestamps([
                    'area_id' => $areaIds[$area] ?? null,
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'curp' => $this->fakeCurp($numero),
                    'rfc' => $this->fakeRfc($numero),
                    'puesto' => $puesto,
                    'fecha_ingreso' => '2020-01-15',
                    'es_lider' => 1,
                    'dias_vacaciones' => 18,
                    'dias_vacaciones_usados' => 4,
                    'activo' => 1,
                ], 'empleados')
            );
        }

        $liderIds = DB::table('empleados')->where('es_lider', 1)->pluck('id', 'area_id');

        $empleados = [
            ['E1001', 'Juan Pérez López', 'juan.perez@prosalon.mx', 'Sistemas / TI', 'Desarrollador Web', '2022-03-10', 14, 5],
            ['E1002', 'María Fernanda López', 'maria.lopez@prosalon.mx', 'Ventas', 'Ejecutivo Comercial JR', '2021-07-01', 16, 6],
            ['E1003', 'Pedro García Torres', 'pedro.garcia@prosalon.mx', 'Almacén', 'Supervisor de Almacén', '2023-02-15', 12, 2],
            ['E1004', 'Ana Sofía Hernández', 'ana.hernandez@prosalon.mx', 'Recursos Humanos', 'Analista de Atracción de Talento', '2020-09-20', 18, 8],
            ['E1005', 'Luis Miguel Ramírez', 'luis.ramirez@prosalon.mx', 'Marketing', 'Community Manager', '2024-01-08', 12, 1],
            ['E1006', 'Daniela Cruz Flores', 'daniela.cruz@prosalon.mx', 'Atención al Cliente', 'Ejecutiva de Atención a Cliente', '2022-11-12', 14, 3],
            ['E1007', 'Carlos Alberto Medina', 'carlos.medina@prosalon.mx', 'Sistemas / TI', 'Ingeniero de Soporte Técnico TI', '2021-04-18', 16, 7],
            ['E1008', 'Gabriela Navarro Ruiz', 'gabriela.navarro@prosalon.mx', 'Ventas', 'Ejecutivo Comercial SR', '2019-06-01', 20, 10],
            ['E1009', 'Oscar Iván Sánchez', 'oscar.sanchez@prosalon.mx', 'Almacén', 'Operador de Producción', '2024-05-20', 12, 0],
            ['E1010', 'Fernanda Castillo Vega', 'fernanda.castillo@prosalon.mx', 'Recursos Humanos', 'Auxiliar de Recursos Humanos', '2023-10-02', 12, 2],
        ];

        foreach ($empleados as [$numero, $nombre, $correo, $area, $puesto, $ingreso, $dias, $usados]) {
            $areaId = $areaIds[$area] ?? null;
            DB::table('empleados')->updateOrInsert(
                ['numero_empleado' => $numero],
                $this->withTimestamps([
                    'area_id' => $areaId,
                    'lider_id' => $liderIds[$areaId] ?? null,
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'curp' => $this->fakeCurp($numero),
                    'rfc' => $this->fakeRfc($numero),
                    'puesto' => $puesto,
                    'fecha_ingreso' => $ingreso,
                    'es_lider' => 0,
                    'dias_vacaciones' => $dias,
                    'dias_vacaciones_usados' => $usados,
                    'activo' => 1,
                ], 'empleados')
            );
        }
    }

    private function seedTiposPermisos(): void
    {
        if (! Schema::hasTable('tipos_permisos')) {
            return;
        }

        $tipos = [
            ['Vacaciones', 'vacaciones', 1, 1],
            ['Permiso con goce de sueldo', 'permiso-con-goce', 0, 0],
            ['Permiso sin goce de sueldo', 'permiso-sin-goce', 0, 0],
            ['Otro permiso', 'otro-permiso', 0, 0],
        ];

        foreach ($tipos as [$nombre, $slug, $descuenta, $saldo]) {
            DB::table('tipos_permisos')->updateOrInsert(
                ['slug' => $slug],
                $this->withTimestamps([
                    'nombre' => $nombre,
                    'descuenta_vacaciones' => $descuenta,
                    'requiere_saldo' => $saldo,
                    'activo' => 1,
                ], 'tipos_permisos')
            );
        }
    }

    private function seedPerfilesPuestoDemo(): void
    {
        if (! Schema::hasTable('perfiles_puesto')) {
            return;
        }

        $perfiles = [
            ['ATC-001', 'Atención al Cliente', 'Atención al Cliente', 'Gerente de Marketing', 'Atender consultas, resolver problemas y dar seguimiento a clientes.', 'Garantizar la satisfacción del cliente.', 'Carrera técnica o licenciatura. Experiencia mínima de 2 años.', 'Comunicación, paciencia, empatía.', 'Resolución de problemas, CRM, manejo de quejas.', 'Responder llamadas, correos, mensajes y documentar interacciones.', 'Licenciatura', '2 años', 'Intermedio', 'CRM, Office, Desk'],
            ['SIS-001', 'Desarrollador Web', 'Sistemas / TI', 'Jefe de Sistemas', 'Desarrollar y mantener aplicaciones internas.', 'Automatizar procesos y mejorar sistemas internos.', 'Ingeniería o licenciatura en sistemas.', 'Analítico, ordenado, responsable.', 'PHP, Laravel, MySQL, Git.', 'Crear módulos, corregir errores y documentar cambios.', 'Licenciatura', '3 a 5 años', 'Intermedio', 'PHP, Laravel, MySQL, Git'],
            ['RH-001', 'Analista de Atracción de Talento', 'Recursos Humanos', 'Gerente de Recursos Humanos', 'Gestionar reclutamiento y entrevistas.', 'Cubrir vacantes en tiempo y forma.', 'Licenciatura en psicología, RH o afín.', 'Organización, comunicación, confidencialidad.', 'Entrevistas, filtros, bolsas de trabajo.', 'Publicar vacantes, filtrar candidatos y coordinar entrevistas.', 'Licenciatura', '1 a 2 años', 'Básico', 'Office, RH Flex'],
            ['VEN-001', 'Ejecutivo Comercial JR', 'Ventas', 'Gerente Comercial', 'Prospectar y atender clientes.', 'Incrementar cartera y ventas.', 'Preparatoria o licenciatura.', 'Negociación, servicio, seguimiento.', 'Ventas, CRM, atención al cliente.', 'Prospectar, cotizar, cerrar ventas y dar seguimiento.', 'Preparatoria', '1 a 2 años', 'Básico', 'CRM, Office'],
            ['ALM-001', 'Supervisor de Almacén', 'Almacén', 'Jefe de Almacén', 'Supervisar entradas, salidas e inventarios.', 'Asegurar control operativo de almacén.', 'Bachillerato o carrera técnica.', 'Orden, liderazgo, responsabilidad.', 'Inventarios, logística, manejo de personal.', 'Supervisar equipo, inventarios y surtido de pedidos.', 'Bachillerato', '3 a 5 años', 'Ninguno', 'Excel, sistema interno'],
            ['MKT-001', 'Community Manager', 'Marketing', 'Gerente de Mercadotecnia', 'Gestionar redes sociales y comunidad digital.', 'Fortalecer presencia digital de marca.', 'Licenciatura en comunicación o marketing.', 'Creatividad, redacción, organización.', 'Redes sociales, copywriting, métricas.', 'Programar contenido, responder comunidad y reportar resultados.', 'Licenciatura', '1 a 2 años', 'Intermedio', 'Meta Business, Canva, Office'],
            ['CAL-001', 'Inspector de Calidad', 'Aseguramiento de Calidad', 'Jefe de Aseguramiento de Calidad', 'Verificar cumplimiento de estándares de calidad.', 'Reducir defectos y asegurar calidad del producto.', 'Carrera técnica o ingeniería.', 'Observación, disciplina, atención al detalle.', 'Inspección, registros, normas de calidad.', 'Inspeccionar producto y documentar hallazgos.', 'Carrera técnica', '1 a 2 años', 'Ninguno', 'Excel'],
            ['MAN-001', 'Técnico Mecánico', 'Mantenimiento', 'Jefe de Mantenimiento', 'Mantener equipos mecánicos en operación.', 'Reducir paros por fallas mecánicas.', 'Técnico mecánico o afín.', 'Responsabilidad, solución de problemas.', 'Mecánica industrial, mantenimiento preventivo.', 'Ejecutar mantenimiento y registrar actividades.', 'Carrera técnica', '3 a 5 años', 'Ninguno', 'N/A'],
            ['COM-001', 'Comprador JR', 'Compras', 'Jefe de Compras', 'Apoyar procesos de compra y negociación.', 'Asegurar abasto oportuno.', 'Licenciatura administrativa o afín.', 'Negociación, orden, análisis.', 'Compras, proveedores, cotizaciones.', 'Solicitar cotizaciones, generar órdenes y seguimiento.', 'Licenciatura', '1 a 2 años', 'Básico', 'Netsuite, Office'],
            ['LAB-001', 'Analista de Laboratorio', 'Laboratorio', 'Jefe de Laboratorio', 'Realizar análisis y pruebas de laboratorio.', 'Asegurar resultados confiables para producción.', 'Químico, laboratorista o afín.', 'Precisión, orden, disciplina.', 'Buenas prácticas de laboratorio.', 'Realizar pruebas, registrar resultados y reportar desviaciones.', 'Licenciatura', '1 a 2 años', 'Básico', 'Excel, sistema laboratorio'],
        ];

        foreach ($perfiles as $p) {
            [$codigo, $nombre, $area, $reporta, $desc, $objetivo, $req, $cualidades, $habilidades, $responsabilidades, $escolaridad, $experiencia, $ingles, $software] = $p;
            $uniqueKey = Str::slug($codigo);

            DB::table('perfiles_puesto')->updateOrInsert(
                ['unique_key' => $uniqueKey],
                $this->withTimestamps([
                    'codigo' => $codigo,
                    'nombre_puesto' => $nombre,
                    'slug' => Str::slug($nombre . '-' . $area),
                    'area_departamento' => $area,
                    'puesto_reporta' => $reporta,
                    'descripcion_puesto' => $desc,
                    'objetivo_puesto' => $objetivo,
                    'requerimientos_minimos' => $req,
                    'cualidades' => $cualidades,
                    'habilidades' => $habilidades,
                    'responsabilidades' => $responsabilidades,
                    'escolaridad_detectada' => $escolaridad,
                    'experiencia_detectada' => $experiencia,
                    'ingles_detectado' => $ingles,
                    'software_detectado' => $software,
                    'texto_original' => 'Registro demo generado por seeder.',
                    'activo' => 1,
                    'importado_at' => now(),
                ], 'perfiles_puesto')
            );
        }
    }

    private function seedCatalogosBase(): void
    {
        if (! Schema::hasTable('catalogos')) {
            return;
        }

        $items = [
            'causa_vacante' => ['Reemplazo puesto vacante', 'Puesto de nueva creación'],
            'tipo_contrato' => ['Temporal', 'Planta', 'Por proyecto'],
            'ubicacion_puesto' => ['Oficinas', 'Acatlán', 'Viñedo', 'Punta Mita', 'Otra'],
            'horario_laboral' => ['Lunes a viernes de 8:00 a 18:00', 'Lunes a Jueves de 8:30 a 18:00 y viernes de 8:30 a 14:30', 'Lunes a sábado de 6:00 a 13:30', 'Domingo a viernes de 14:00 a 21:30', 'Otro'],
            'software_requerido' => ['Office', 'Netsuite', 'RH Flex', 'Power BI', 'Desk', 'Otras'],
            'hardware_requerido' => ['Computadora de escritorio', 'Laptop', 'Celular', 'Otras'],
            'escolaridad' => ['Primaria', 'Secundaria', 'Preparatoria / Bachillerato', 'Carrera técnica', 'Licenciatura', 'Ingeniería', 'Maestría', 'Indistinto'],
            'sexo_requisicion' => ['Hombre', 'Mujer', 'Indistinto'],
            'si_no' => ['Si', 'No'],
            'anios_experiencia' => ['0 a 1 año', '1 a 2 años', '3 a 5 años', 'Otras'],
            'nivel_ingles' => ['Ninguno', 'Básico', 'Intermedio', 'Avanzado'],
        ];

        foreach (DB::table('perfiles_puesto')->distinct()->pluck('area_departamento') as $area) {
            if ($area) {
                $items['departamento'][] = $area;
            }
        }

        foreach (DB::table('perfiles_puesto')->distinct()->pluck('puesto_reporta') as $puesto) {
            if ($puesto) {
                $items['puesto_reporta'][] = $puesto;
            }
        }

        foreach ($items as $tipo => $valores) {
            foreach (array_unique($valores) as $valor) {
                DB::table('catalogos')->updateOrInsert(
                    ['tipo' => $tipo, 'valor' => $valor],
                    $this->withTimestamps(['tipo' => $tipo, 'valor' => $valor], 'catalogos')
                );
            }
        }
    }

    private function seedFormulariosDemo(): void
    {
        if (! Schema::hasTable('formularios') || ! Schema::hasTable('form_fields')) {
            return;
        }

        $this->crearFormulario('Requisición de Personal', 'requisicion-personal', 'Formulario para solicitar una vacante o reemplazo de personal.', [
            ['departamento_solicitante', 'Departamento solicitante', 'select', 1, 'db', 'departamento', 'I. Departamento solicitante'],
            ['causa_vacante', 'Causa de la vacante', 'radio', 1, 'db', 'causa_vacante', 'II. Vacante y contrato'],
            ['tipo_contrato', 'Tipo de contrato', 'radio', 1, 'db', 'tipo_contrato', 'II. Vacante y contrato'],
            ['area_departamento_puesto', 'Área o departamento del puesto', 'select', 1, 'db', 'departamento', 'III. Datos del puesto'],
            ['perfil_puesto_id', 'Perfil de puesto base', 'select', 0, null, null, 'III. Datos del puesto'],
            ['nombre_puesto', 'Nombre del puesto', 'text', 1, null, null, 'III. Datos del puesto'],
            ['ubicacion_fisica_puesto', 'Ubicación física del puesto', 'select', 1, 'db', 'ubicacion_puesto', 'III. Datos del puesto'],
            ['horario_jornada_laboral', 'Horario de jornada laboral', 'select', 1, 'db', 'horario_laboral', 'III. Datos del puesto'],
            ['puesto_a_quien_reporta', 'Puesto a quien reporta', 'select', 1, 'db', 'puesto_reporta', 'III. Datos del puesto'],
            ['software_requerido', 'Requerimientos de licencias o software', 'checkbox', 0, 'db', 'software_requerido', 'III. Datos del puesto'],
            ['hardware_requerido', 'Requerimientos de hardware y/o equipos', 'checkbox', 0, 'db', 'hardware_requerido', 'III. Datos del puesto'],
            ['requiere_correo_electronico', 'Requiere correo electrónico', 'radio', 1, 'db', 'si_no', 'III. Datos del puesto'],
            ['funciones_generales_puesto', 'Funciones generales del puesto', 'textarea', 1, null, null, 'III. Datos del puesto'],
            ['escolaridad', 'Escolaridad o grado académico', 'select', 1, 'db', 'escolaridad', 'IV. Perfil requerido'],
            ['rango_edad', 'Rango de edad', 'text', 1, null, null, 'IV. Perfil requerido'],
            ['sexo', 'Sexo', 'radio', 1, 'db', 'sexo_requisicion', 'IV. Perfil requerido'],
            ['disponibilidad_viajar', 'Disponibilidad para viajar', 'radio', 1, 'db', 'si_no', 'IV. Perfil requerido'],
            ['area_experiencia', 'Área de experiencia', 'textarea', 1, null, null, 'IV. Perfil requerido'],
            ['anios_experiencia', 'Años de experiencia', 'radio', 1, 'db', 'anios_experiencia', 'IV. Perfil requerido'],
            ['conocimientos_indispensables', 'Conocimientos específicos indispensables', 'textarea', 1, null, null, 'IV. Perfil requerido'],
            ['conocimientos_deseables', 'Conocimientos específicos deseables', 'textarea', 0, null, null, 'IV. Perfil requerido'],
            ['habilidades_indispensables', 'Habilidades indispensables', 'textarea', 1, null, null, 'IV. Perfil requerido'],
            ['habilidades_deseables', 'Habilidades deseables', 'textarea', 0, null, null, 'IV. Perfil requerido'],
            ['software_especifico', 'Habilidades y/o conocimientos específicos de software', 'textarea', 1, null, null, 'IV. Perfil requerido'],
            ['hardware_especifico', 'Habilidades y/o conocimientos de hardware requeridos', 'textarea', 0, null, null, 'IV. Perfil requerido'],
            ['nivel_ingles', 'Nivel de inglés requerido', 'radio', 1, 'db', 'nivel_ingles', 'IV. Perfil requerido'],
            ['notas_observaciones', 'Notas u observaciones', 'textarea', 0, null, null, 'IV. Perfil requerido'],
        ]);

        $this->crearFormulario('Solicitud de Capacitación', 'solicitud-capacitacion', 'Solicitud interna para cursos, talleres o capacitaciones.', [
            ['nombre_colaborador', 'Nombre del colaborador', 'text', 1, null, null, 'Datos generales'],
            ['departamento', 'Departamento', 'select', 1, 'db', 'departamento', 'Datos generales'],
            ['puesto', 'Puesto', 'text', 1, null, null, 'Datos generales'],
            ['nombre_capacitacion', 'Nombre de la capacitación', 'text', 1, null, null, 'Capacitación'],
            ['objetivo_capacitacion', 'Objetivo de la capacitación', 'textarea', 1, null, null, 'Capacitación'],
            ['fecha_sugerida', 'Fecha sugerida', 'date', 0, null, null, 'Capacitación'],
            ['costo_estimado', 'Costo estimado', 'number', 0, null, null, 'Capacitación'],
            ['comentarios', 'Comentarios', 'textarea', 0, null, null, 'Capacitación'],
        ]);

        $this->crearFormulario('Cambio de Datos de Colaborador', 'cambio-datos-colaborador', 'Actualización de datos personales o laborales.', [
            ['numero_empleado', 'Número de empleado', 'text', 1, null, null, 'Identificación'],
            ['nombre_colaborador', 'Nombre del colaborador', 'text', 1, null, null, 'Identificación'],
            ['dato_a_cambiar', 'Dato a cambiar', 'select', 1, 'Nombre,Correo,Teléfono,Domicilio,Puesto,Departamento,Otro', null, 'Cambio solicitado'],
            ['valor_actual', 'Valor actual', 'textarea', 1, null, null, 'Cambio solicitado'],
            ['valor_nuevo', 'Valor nuevo', 'textarea', 1, null, null, 'Cambio solicitado'],
            ['motivo', 'Motivo del cambio', 'textarea', 0, null, null, 'Cambio solicitado'],
        ]);

        $this->crearFormulario('Solicitud de Equipo o Herramientas', 'solicitud-equipo-herramientas', 'Solicitud de equipo, hardware, software o herramientas de trabajo.', [
            ['nombre_solicitante', 'Nombre del solicitante', 'text', 1, null, null, 'Datos generales'],
            ['departamento', 'Departamento', 'select', 1, 'db', 'departamento', 'Datos generales'],
            ['tipo_requerimiento', 'Tipo de requerimiento', 'checkbox', 1, 'db', 'hardware_requerido', 'Solicitud'],
            ['software_requerido', 'Software requerido', 'checkbox', 0, 'db', 'software_requerido', 'Solicitud'],
            ['justificacion', 'Justificación', 'textarea', 1, null, null, 'Solicitud'],
            ['fecha_requerida', 'Fecha requerida', 'date', 0, null, null, 'Solicitud'],
        ]);

        $this->crearFormulario('Evaluación de Desempeño', 'evaluacion-desempeno', 'Evaluación interna de desempeño del colaborador.', [
            ['nombre_colaborador', 'Nombre del colaborador', 'text', 1, null, null, 'Datos generales'],
            ['departamento', 'Departamento', 'select', 1, 'db', 'departamento', 'Datos generales'],
            ['puesto', 'Puesto', 'text', 1, null, null, 'Datos generales'],
            ['periodo_evaluado', 'Periodo evaluado', 'text', 1, null, null, 'Evaluación'],
            ['cumplimiento_objetivos', 'Cumplimiento de objetivos', 'select', 1, 'Excelente,Bueno,Regular,Bajo', null, 'Evaluación'],
            ['fortalezas', 'Fortalezas', 'textarea', 1, null, null, 'Evaluación'],
            ['areas_mejora', 'Áreas de mejora', 'textarea', 1, null, null, 'Evaluación'],
            ['plan_accion', 'Plan de acción', 'textarea', 0, null, null, 'Evaluación'],
        ]);
    }

    private function seedSolicitudesPermisosDemo(): void
    {
        if (! Schema::hasTable('permisos_solicitudes') || ! Schema::hasTable('empleados') || ! Schema::hasTable('tipos_permisos')) {
            return;
        }

        $empleados = DB::table('empleados')->where('es_lider', 0)->limit(5)->get();
        $vacaciones = DB::table('tipos_permisos')->where('slug', 'vacaciones')->value('id');
        $goce = DB::table('tipos_permisos')->where('slug', 'permiso-con-goce')->value('id');

        foreach ($empleados as $i => $empleado) {
            DB::table('permisos_solicitudes')->updateOrInsert(
                ['empleado_id' => $empleado->id, 'fecha_inicio' => now()->addDays(10 + $i)->toDateString()],
                $this->withTimestamps([
                    'tipo_permiso_id' => $i % 2 === 0 ? $vacaciones : $goce,
                    'area_id' => $empleado->area_id ?? null,
                    'lider_id' => $empleado->lider_id ?? null,
                    'fecha_fin' => now()->addDays(11 + $i)->toDateString(),
                    'dias_solicitados' => 2,
                    'motivo' => $i % 2 === 0 ? 'Vacaciones demo.' : 'Permiso con goce demo.',
                    'estatus' => $i === 0 ? 'formato_recibido' : 'formato_pendiente',
                    'formato_recibido' => $i === 0 ? 1 : 0,
                ], 'permisos_solicitudes')
            );
        }
    }

    private function crearFormulario(string $titulo, string $slug, string $descripcion, array $fields): void
    {
        $formData = $this->onlyExisting('formularios', [
            'titulo' => $titulo,
            'slug' => $slug,
            'descripcion' => $descripcion,
            'mail_to' => 'rhformularios@prosalon.mx',
            'template_path' => null,
            'activo' => 1,
            'es_default' => 0,
        ]);

        DB::table('formularios')->updateOrInsert(['slug' => $slug], $this->withTimestamps($formData, 'formularios'));

        $formId = DB::table('formularios')->where('slug', $slug)->value('id');

        foreach ($fields as $index => $field) {
            [$name, $label, $type, $required, $source, $table, $section] = $field;
            $payload = $this->onlyExisting('form_fields', [
                'formulario_id' => $formId,
                'name' => $name,
                'label' => $label,
                'type' => $type,
                'required' => $required,
                'visible' => 1,
                'data_source' => $source,
                'data_table' => $table,
                'section' => $section,
                'orden' => $index + 1,
            ]);

            DB::table('form_fields')->updateOrInsert(
                ['formulario_id' => $formId, 'name' => $name],
                $this->withTimestamps($payload, 'form_fields')
            );
        }
    }

    private function withTimestamps(array $data, string $table): array
    {
        $data = $this->onlyExisting($table, $data);

        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        if (Schema::hasColumn($table, 'created_at') && ! array_key_exists('created_at', $data)) {
            $data['created_at'] = now();
        }

        return $data;
    }

    private function onlyExisting(string $table, array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $key) => Schema::hasColumn($table, $key))
            ->toArray();
    }

    private function fakeCurp(string $numero): string
    {
        $n = preg_replace('/\D/', '', $numero) ?: '0000';
        return 'DEMO900101HJC' . str_pad(substr($n, -4), 5, '0', STR_PAD_LEFT);
    }

    private function fakeRfc(string $numero): string
    {
        $n = preg_replace('/\D/', '', $numero) ?: '0000';
        return 'DEMO900101' . str_pad(substr($n, -3), 3, '0', STR_PAD_LEFT);
    }
}
