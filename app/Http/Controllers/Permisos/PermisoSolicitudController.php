<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Mail\PermisoDocumentoFisicoMail;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\PermisoSolicitud;
use App\Models\PermisoHistorial;
use App\Models\TipoPermiso;
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

    public function store(Request $request, PermisoSaldoService $saldoService, PermisoDocumentoService $documentoService)
    {
        $validated = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'tipo_permiso_id' => ['required', 'exists:tipos_permisos,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'dias_solicitados' => ['required', 'numeric', 'min:0.5'],
            'motivo' => ['nullable', 'string', 'max:3000'],
        ], [
            'empleado_id.required' => 'Selecciona un colaborador de la lista.',
            'tipo_permiso_id.required' => 'Selecciona el tipo de permiso.',
        ]);

        $empleado = Empleado::with(['area', 'lider'])->findOrFail($validated['empleado_id']);
        $tipoPermiso = TipoPermiso::findOrFail($validated['tipo_permiso_id']);

        $cruce = PermisoSolicitud::existeCruceDeFechas(
            $empleado->id,
            $validated['fecha_inicio'],
            $validated['fecha_fin']
        );

        if ($cruce) {
            return back()->withInput()->with('error', 'Este colaborador ya tiene una solicitud activa que cruza con esas fechas: #' . $cruce->id . ' del ' . $cruce->fecha_inicio?->format('d/m/Y') . ' al ' . $cruce->fecha_fin?->format('d/m/Y') . '.');
        }

        if (! $saldoService->validarSaldoSuficiente($empleado, $tipoPermiso, (float) $validated['dias_solicitados'])) {
            $saldo = $saldoService->resumen($empleado);

            return back()->withInput()->with('error', 'No puedes solicitar ' . $validated['dias_solicitados'] . ' días. Disponible actual de vacaciones: ' . $saldo['dias_disponibles'] . ' días. Los pendientes de formato no descuentan hasta que RH marque formato recibido.');
        }

        try {
            $solicitud = DB::transaction(function () use ($validated, $empleado) {
                return PermisoSolicitud::create([
                    'tipo_permiso_id' => $validated['tipo_permiso_id'],
                    'empleado_id' => $empleado->id,
                    'area_id' => $empleado->area_id,
                    'lider_id' => $empleado->lider_id,
                    'fecha_inicio' => Carbon::parse($validated['fecha_inicio']),
                    'fecha_fin' => Carbon::parse($validated['fecha_fin']),
                    'dias_solicitados' => $validated['dias_solicitados'],
                    'motivo' => $validated['motivo'] ?? null,
                    'estatus' => 'formato_generado',
                    'formato_recibido' => false,
                ]);
            });

            PermisoHistorial::create([
                'permiso_solicitud_id' => $solicitud->id,
                'accion' => 'solicitud_creada',
                'descripcion' => 'El colaborador registró una nueva solicitud.',
            ]);

            $documentoRelativePath = $documentoService->generarDocumento($solicitud);
            $documentoAbsolutePath = $documentoService->absolutePath($documentoRelativePath);

            $this->enviarDocumento($solicitud->fresh(['empleado', 'lider', 'area', 'tipoPermiso']), $documentoAbsolutePath);

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
                Mail::to($item['correo'])->send(new PermisoDocumentoFisicoMail($solicitud, $documentoAbsolutePath, $item['tipo']));

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
