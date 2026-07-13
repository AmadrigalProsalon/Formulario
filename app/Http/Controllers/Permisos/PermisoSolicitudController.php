<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Mail\PermisoDocumentoFisicoMail;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\PermisoHistorial;
use App\Models\PermisoSolicitud;
use App\Models\TipoPermiso;
use App\Services\Permisos\CalendarioLaboralService;
use App\Services\Permisos\PermisoDocumentoService;
use App\Services\Permisos\PermisoSaldoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PermisoSolicitudController extends Controller
{
    public function create()
    {
        return view('permisos.solicitud', [
            'areas' => Area::where('activo', true)->orderBy('nombre')->get(),
            'tiposPermisos' => TipoPermiso::where('activo', true)->orderBy('nombre')->get(),
            'firmaDigitalActiva' => config('permisos.firma_digital', false),
        ]);
    }

    public function validarFechas(Request $request, CalendarioLaboralService $calendarioService)
    {
        $validated = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'fechas' => ['required', 'array', 'min:1', 'max:60'],
            'fechas.*' => ['required', 'date', 'distinct'],
        ]);

        $empleado = Empleado::with('area')->findOrFail($validated['empleado_id']);

        return response()->json($calendarioService->validarFechas($empleado, $validated['fechas']));
    }

    public function store(
        Request $request,
        PermisoSaldoService $saldoService,
        PermisoDocumentoService $documentoService,
        CalendarioLaboralService $calendarioService
    ) {
        $base = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'tipo_permiso_id' => ['required', 'exists:tipos_permisos,id'],
            'motivo' => ['nullable', 'string', 'max:3000'],
        ], [
            'empleado_id.required' => 'Selecciona un colaborador de la lista.',
            'tipo_permiso_id.required' => 'Selecciona el tipo de permiso.',
        ]);

        $empleado = Empleado::with(['area', 'lider'])->findOrFail($base['empleado_id']);
        $tipoPermiso = TipoPermiso::findOrFail($base['tipo_permiso_id']);
        $esVacaciones = $tipoPermiso->slug === 'vacaciones' || $tipoPermiso->descuenta_vacaciones;

        if ($esVacaciones) {
            $validated = array_merge($base, $request->validate([
                'fechas_seleccionadas' => ['required', 'array', 'min:1', 'max:60'],
                'fechas_seleccionadas.*' => ['required', 'date', 'distinct'],
            ], [
                'fechas_seleccionadas.required' => 'Selecciona al menos un día de vacaciones.',
                'fechas_seleccionadas.min' => 'Selecciona al menos un día de vacaciones.',
                'fechas_seleccionadas.max' => 'No puedes seleccionar más de 60 fechas en una sola solicitud.',
            ]));

            $fechas = $calendarioService->normalizarFechas($validated['fechas_seleccionadas']);

            if (count($fechas) !== count($validated['fechas_seleccionadas'])) {
                return back()->withInput()->with('error', 'Las fechas de vacaciones no pueden repetirse ni tener un formato inválido.');
            }

            $resultadoCalendario = $calendarioService->validarFechas($empleado, $fechas);

            if (! empty($resultadoCalendario['invalidas'])) {
                $detalle = collect($resultadoCalendario['invalidas'])
                    ->map(fn ($item) => $item['fecha_formato'] . ': ' . $item['motivo'])
                    ->take(8)
                    ->implode(' | ');

                return back()->withInput()->with('error', 'Hay fechas no permitidas: ' . $detalle);
            }

            $diasSolicitados = (float) count($fechas);
            $fechaInicio = $fechas[0];
            $fechaFin = $fechas[count($fechas) - 1];

            $cruce = $this->buscarCrucePorDias($empleado->id, $fechas);
        } else {
            $validated = array_merge($base, $request->validate([
                'fecha_inicio' => ['required', 'date'],
                'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
                'dias_solicitados' => ['required', 'numeric', 'min:0.5'],
            ]));

            $fechaInicio = $validated['fecha_inicio'];
            $fechaFin = $validated['fecha_fin'];
            $fechas = [];
            $diasSolicitados = (float) $validated['dias_solicitados'];

            $cruce = $this->buscarCrucePorRango(
                $empleado->id,
                $fechaInicio,
                $fechaFin
            );
        }

        if ($cruce) {
            return back()->withInput()->with(
                'error',
                'Este colaborador ya tiene una solicitud activa que cruza con una de las fechas seleccionadas: #' . $cruce->id . '.'
            );
        }

        if (! $saldoService->validarSaldoSuficiente($empleado, $tipoPermiso, $diasSolicitados)) {
            $saldo = $saldoService->resumen($empleado);

            return back()->withInput()->with(
                'error',
                'No puedes solicitar ' . $diasSolicitados . ' día(s). Disponible actual de vacaciones: ' .
                $saldo['dias_disponibles'] .
                ' día(s). Los pendientes de formato no descuentan hasta que RH marque formato recibido.'
            );
        }

        try {
            $solicitud = DB::transaction(function () use (
                $validated,
                $empleado,
                $fechaInicio,
                $fechaFin,
                $fechas,
                $diasSolicitados
            ) {
                $solicitud = PermisoSolicitud::create([
                    'tipo_permiso_id' => $validated['tipo_permiso_id'],
                    'empleado_id' => $empleado->id,
                    'area_id' => $empleado->area_id,
                    'lider_id' => $empleado->lider_id,
                    'fecha_inicio' => Carbon::parse($fechaInicio),
                    'fecha_fin' => Carbon::parse($fechaFin),
                    'dias_solicitados' => $diasSolicitados,
                    'motivo' => $validated['motivo'] ?? null,
                    'estatus' => 'formato_generado',
                    'formato_recibido' => false,
                ]);

                foreach ($fechas as $fecha) {
                    $solicitud->diasSeleccionados()->create(['fecha' => $fecha]);
                }

                return $solicitud;
            });

            PermisoHistorial::create([
                'permiso_solicitud_id' => $solicitud->id,
                'accion' => 'solicitud_creada',
                'descripcion' => 'El colaborador registró una nueva solicitud.',
            ]);

            $documentoRelativePath = $documentoService->generarDocumento($solicitud);
            $documentoAbsolutePath = $documentoService->absolutePath($documentoRelativePath);

            $this->enviarDocumento(
                $solicitud->fresh(['empleado', 'lider', 'area', 'tipoPermiso', 'diasSeleccionados']),
                $documentoAbsolutePath
            );

            $solicitud->update([
                'estatus' => 'formato_enviado',
                'documento_enviado_at' => now(),
            ]);

            PermisoHistorial::create([
                'permiso_solicitud_id' => $solicitud->id,
                'accion' => 'documento_enviado',
                'descripcion' => 'El formato fue enviado al colaborador, líder y RH.',
            ]);

            return redirect()->route('permisos.solicitud.gracias')
                ->with('success', 'Solicitud registrada. El formato fue enviado al líder, colaborador y RH para seguimiento físico.');
        } catch (Throwable $e) {
            Log::error('Error al crear solicitud de permiso', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Ocurrió un error al generar o enviar el formato. Contacta a Sistemas.');
        }
    }


    private function buscarCrucePorRango(int $empleadoId, string $fechaInicio, string $fechaFin): ?PermisoSolicitud
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();

        $solicitudes = PermisoSolicitud::with('diasSeleccionados')
            ->where('empleado_id', $empleadoId)
            ->whereIn('estatus', config('permisos.estatus_activos_para_cruce', [
                'formato_generado',
                'formato_enviado',
                'formato_pendiente',
                'formato_recibido',
                'con_observaciones',
            ]))
            ->whereDate('fecha_inicio', '<=', $fin->format('Y-m-d'))
            ->whereDate('fecha_fin', '>=', $inicio->format('Y-m-d'))
            ->get();

        foreach ($solicitudes as $solicitud) {
            if ($solicitud->diasSeleccionados->isNotEmpty()) {
                $cruza = $solicitud->diasSeleccionados->contains(
                    fn ($dia) => $dia->fecha->betweenIncluded($inicio, $fin)
                );

                if ($cruza) {
                    return $solicitud;
                }

                continue;
            }

            return $solicitud;
        }

        return null;
    }

    private function buscarCrucePorDias(int $empleadoId, array $fechas): ?PermisoSolicitud
    {
        if (! $fechas) {
            return null;
        }

        $inicio = min($fechas);
        $fin = max($fechas);

        $solicitudes = PermisoSolicitud::with('diasSeleccionados')
            ->where('empleado_id', $empleadoId)
            ->whereIn('estatus', config('permisos.estatus_activos_para_cruce', [
                'formato_generado',
                'formato_enviado',
                'formato_pendiente',
                'formato_recibido',
                'con_observaciones',
            ]))
            ->whereDate('fecha_inicio', '<=', $fin)
            ->whereDate('fecha_fin', '>=', $inicio)
            ->get();

        foreach ($solicitudes as $solicitud) {
            $diasGuardados = $solicitud->diasSeleccionados
                ->map(fn ($dia) => $dia->fecha->format('Y-m-d'))
                ->all();

            if ($diasGuardados) {
                if (array_intersect($fechas, $diasGuardados)) {
                    return $solicitud;
                }

                continue;
            }

            foreach ($fechas as $fecha) {
                if (Carbon::parse($fecha)->betweenIncluded($solicitud->fecha_inicio, $solicitud->fecha_fin)) {
                    return $solicitud;
                }
            }
        }

        return null;
    }

    private function enviarDocumento(PermisoSolicitud $solicitud, string $documentoAbsolutePath): void
    {
        $rhEmail = config('permisos.rh_email');
        $correos = [];

        if ($solicitud->empleado?->correo) {
            $correos[] = ['correo' => $solicitud->empleado->correo, 'tipo' => 'colaborador'];
        }

        if ($solicitud->lider?->correo) {
            $correos[] = ['correo' => $solicitud->lider->correo, 'tipo' => 'lider'];
        }

        if ($rhEmail) {
            $correos[] = ['correo' => $rhEmail, 'tipo' => 'rh'];
        }

        foreach ($correos as $item) {
            try {
                Mail::to($item['correo'])->send(
                    new PermisoDocumentoFisicoMail($solicitud, $documentoAbsolutePath, $item['tipo'])
                );

                DB::table('permiso_documento_envios')->insert([
                    'permiso_solicitud_id' => $solicitud->id,
                    'correo' => $item['correo'],
                    'tipo' => 'documento_' . $item['tipo'],
                    'estatus' => 'enviado',
                    'enviado_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (Throwable $e) {
                DB::table('permiso_documento_envios')->insert([
                    'permiso_solicitud_id' => $solicitud->id,
                    'correo' => $item['correo'],
                    'tipo' => 'documento_' . $item['tipo'],
                    'estatus' => 'error',
                    'error' => $e->getMessage(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function gracias()
    {
        return view('permisos.gracias');
    }
}
