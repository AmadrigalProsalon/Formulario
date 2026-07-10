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

        if (! Schema::hasTable('permiso_solicitud_dias')) {
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

        $this->seedHorariosBase();
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_solicitud_dias');

        if (Schema::hasTable('empleados') && Schema::hasColumn('empleados', 'dias_laborales')) {
            Schema::table('empleados', fn (Blueprint $table) => $table->dropColumn('dias_laborales'));
        }

        if (Schema::hasTable('areas') && Schema::hasColumn('areas', 'dias_laborales')) {
            Schema::table('areas', fn (Blueprint $table) => $table->dropColumn('dias_laborales'));
        }
    }

    private function seedHorariosBase(): void
    {
        if (! Schema::hasTable('areas') || ! Schema::hasTable('empleados')) {
            return;
        }

        $normalizar = fn (?string $texto) => Str::lower(Str::ascii(trim((string) $texto)));

        $areas = DB::table('areas')->select('id', 'nombre')->get();

        foreach ($areas as $area) {
            $nombre = $normalizar($area->nombre);
            $dias = null;

            if (str_contains($nombre, 'almacen vespertino')) {
                $dias = [7, 1, 2, 3, 4, 5];
            } elseif (str_contains($nombre, 'almacen matutino')) {
                $dias = [1, 2, 3, 4, 5, 6];
            } elseif (str_contains($nombre, 'oficina')) {
                $dias = [1, 2, 3, 4, 5];
            } elseif (str_contains($nombre, 'acatlan')) {
                $dias = [1, 2, 3, 4, 5];
            } elseif (str_contains($nombre, 'almacenista') && str_contains($nombre, 'acatlan')) {
                $dias = [1, 2, 3, 4, 5];
            }

            if ($dias !== null) {
                DB::table('areas')->where('id', $area->id)->update([
                    'dias_laborales' => json_encode($dias),
                    'updated_at' => now(),
                ]);
            }
        }

        $reglasGenerales = [
            'ricardo baltazar' => [5, 6, 7],
            'miguel corona' => [1, 2, 3, 4],
            'jose sanabria' => [1, 2, 3, 4, 5, 6],
            'antonio fernandez' => [1, 2, 3, 4, 5, 6],
            'juan jose' => [1, 2, 3, 4, 5],
            'oscar ivan' => [5, 6, 7, 1],
            'victor manuel santos' => [1, 2, 3, 4, 5, 6],
            'jesus cardenas' => [1, 2, 3, 4, 5, 6],
            'cesar alejandro rodriguez' => [1, 2, 3, 4, 5, 6],
            'alejandro pantoja' => [1, 2, 3, 4, 5, 6],
            'christoper rosales' => [7, 1, 2, 3, 4, 5],
            'isabel' => [1, 2, 3, 4, 5, 6],
        ];

        $empleados = DB::table('empleados as e')
            ->leftJoin('areas as a', 'a.id', '=', 'e.area_id')
            ->select('e.id', 'e.nombre', 'a.nombre as area_nombre')
            ->get();

        foreach ($empleados as $empleado) {
            $nombre = $normalizar($empleado->nombre);
            $areaNombre = $normalizar($empleado->area_nombre);
            $dias = null;

            foreach ($reglasGenerales as $fragmento => $reglaDias) {
                if (str_contains($nombre, $fragmento)) {
                    $dias = $reglaDias;
                    break;
                }
            }

            if ($dias === null && str_contains($areaNombre, 'punta mita')) {
                $reglasPuntaMita = [
                    'dulce' => [3, 4, 5, 6, 7, 1],
                    'delfina' => [1, 2, 3, 4, 5],
                    'lizeth' => [5, 6, 7],
                    'noemi' => [1, 2, 3, 4, 5, 6],
                    'cecilia' => [4, 5, 6, 7, 1, 2],
                    'jose' => [1, 2, 3, 4, 5, 6],
                    'valentin' => [5, 6, 7, 1, 2, 3],
                    'elsa' => [2, 3, 4, 5, 6, 7],
                    'angel' => [4, 5, 6, 7, 1, 2],
                    'cesar' => [3, 4, 5, 6, 7, 1],
                    'saul' => [5, 6, 7, 1, 2, 3],
                ];

                foreach ($reglasPuntaMita as $fragmento => $reglaDias) {
                    if (str_contains($nombre, $fragmento)) {
                        $dias = $reglaDias;
                        break;
                    }
                }
            }

            if ($dias !== null) {
                DB::table('empleados')->where('id', $empleado->id)->update([
                    'dias_laborales' => json_encode($dias),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
