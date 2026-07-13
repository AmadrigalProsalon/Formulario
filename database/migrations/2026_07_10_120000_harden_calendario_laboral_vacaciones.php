<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('areas') && ! Schema::hasColumn('areas', 'dias_laborales')) {
            Schema::table('areas', function (Blueprint $table) {
                $table->json('dias_laborales')->nullable();
            });
        }

        if (Schema::hasTable('empleados') && ! Schema::hasColumn('empleados', 'dias_laborales')) {
            Schema::table('empleados', function (Blueprint $table) {
                $table->json('dias_laborales')->nullable();
            });
        }

        if (! Schema::hasTable('permiso_solicitud_dias') && Schema::hasTable('permisos_solicitudes')) {
            Schema::create('permiso_solicitud_dias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permiso_solicitud_id')
                    ->constrained('permisos_solicitudes')
                    ->cascadeOnDelete();
                $table->date('fecha');
                $table->timestamps();

                $table->unique(['permiso_solicitud_id', 'fecha'], 'permiso_dia_unico');
                $table->index('fecha');
            });
        }

        $this->aplicarHorariosConocidos();
    }

    public function down(): void
    {
        // No se eliminan datos ni columnas en rollback para proteger configuración productiva.
    }

    private function aplicarHorariosConocidos(): void
    {
        if (! Schema::hasTable('areas') || ! Schema::hasTable('empleados')) {
            return;
        }

        $reglasAreas = (array) config('calendario_laboral.reglas_areas', []);
        $reglasEmpleados = (array) config('calendario_laboral.reglas_empleados', []);
        $reglasPuntaMita = (array) config('calendario_laboral.reglas_punta_mita', []);

        $normalizar = fn (?string $texto): string => Str::lower(Str::ascii(trim((string) $texto)));

        foreach (DB::table('areas')->select('id', 'nombre', 'dias_laborales')->get() as $area) {
            if (! empty($area->dias_laborales)) {
                continue;
            }

            $nombre = $normalizar($area->nombre);

            foreach ($reglasAreas as $fragmento => $dias) {
                if (str_contains($nombre, $normalizar($fragmento))) {
                    DB::table('areas')->where('id', $area->id)->update([
                        'dias_laborales' => json_encode(array_values($dias)),
                        'updated_at' => now(),
                    ]);
                    break;
                }
            }
        }

        $empleados = DB::table('empleados as e')
            ->leftJoin('areas as a', 'a.id', '=', 'e.area_id')
            ->select('e.id', 'e.nombre', 'e.dias_laborales', 'a.nombre as area_nombre')
            ->get();

        foreach ($empleados as $empleado) {
            if (! empty($empleado->dias_laborales)) {
                continue;
            }

            $nombre = $normalizar($empleado->nombre);
            $area = $normalizar($empleado->area_nombre);
            $dias = null;

            foreach ($reglasEmpleados as $fragmento => $reglaDias) {
                if (str_contains($nombre, $normalizar($fragmento))) {
                    $dias = $reglaDias;
                    break;
                }
            }

            if ($dias === null && str_contains($area, 'punta mita')) {
                foreach ($reglasPuntaMita as $fragmento => $reglaDias) {
                    if (str_contains($nombre, $normalizar($fragmento))) {
                        $dias = $reglaDias;
                        break;
                    }
                }
            }

            if ($dias !== null) {
                DB::table('empleados')->where('id', $empleado->id)->update([
                    'dias_laborales' => json_encode(array_values($dias)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
