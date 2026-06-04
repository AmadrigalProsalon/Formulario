<?php

namespace App\Http\Controllers;

use App\Mail\PermisoFirmaMail;
use App\Models\DiaInhabil;
use App\Models\Empleado;
use App\Models\PermisoFirma;
use App\Models\PermisoNotificacion;
use App\Models\PermisoSolicitud;
use App\Models\TipoPermiso;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PermisoPublicController extends Controller
{
    public function create()
    {
        $empleados = Empleado::with(['area', 'lider'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $tipos = TipoPermiso::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('permisos.public.solicitud', compact('empleados', 'tipos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'tipo_permiso_id' => ['required', 'exists:tipos_permisos,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:3000'],
        ], [
            'empleado_id.required' => 'Selecciona al colaborador.',
            'tipo_permiso_id.required' => 'Selecciona el tipo de permiso.',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]);

        $empleado = Empleado::with(['area', 'lider'])->findOrFail($validated['empleado_id']);
        $tipo = TipoPermiso::findOrFail($validated['tipo_permiso_id']);

        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaFin = Carbon::parse($validated['fecha_fin']);
        $dias = $this->calcularDiasHabiles($fechaInicio, $fechaFin);

        if ($dias <= 0) {
            return back()->withInput()->with('error', 'El rango seleccionado no contiene días hábiles descontables.');
        }

        if ($tipo->requiere_saldo && $dias > $empleado->vacaciones_disponibles) {
            return back()->withInput()->with('error', "No puedes solicitar {$dias} días. El colaborador solo tiene {$empleado->vacaciones_disponibles} días disponibles.");
        }

        $solicitud = DB::transaction(function () use ($request, $empleado, $tipo, $fechaInicio, $fechaFin, $dias, $validated) {
            $estatusInicial = $tipo->requiere_firma_colaborador
                ? 'pendiente_firma_colaborador'
                : ($tipo->requiere_firma_lider ? 'pendiente_firma_lider' : 'firmado_completo');

            $solicitud = PermisoSolicitud::create([
                'tipo_permiso_id' => $tipo->id,
                'empleado_id' => $empleado->id,
                'area_id' => $empleado->area_id,
                'lider_id' => $empleado->lider_id,
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
                'dias_solicitados' => $dias,
                'motivo' => $validated['motivo'] ?? null,
                'estatus' => $estatusInicial,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($tipo->descuenta_vacaciones) {
                $empleado->increment('vacaciones_pendientes', $dias);
            }

            if ($tipo->requiere_firma_colaborador) {
                $this->crearFirma($solicitud, $empleado, 'colaborador');
            }

            if ($tipo->requiere_firma_lider && $empleado->lider) {
                $this->crearFirma($solicitud, $empleado->lider, 'lider');
            }

            return $solicitud;
        });

        $this->enviarCorreosFirma($solicitud->fresh(['firmas', 'empleado', 'lider', 'tipoPermiso']));

        return redirect()->route('permisos.gracias')->with('success', 'Solicitud registrada. Se enviaron los enlaces de firma al colaborador y al líder correspondiente.');
    }

    public function gracias()
    {
        return view('permisos.public.gracias');
    }

    public function firma(string $token)
    {
        $firma = PermisoFirma::with(['solicitud.empleado.area', 'solicitud.lider', 'solicitud.tipoPermiso'])
            ->where('token', $token)
            ->firstOrFail();

        return view('permisos.public.firma', compact('firma'));
    }

    public function firmar(Request $request, string $token)
    {
        $firma = PermisoFirma::with(['solicitud.firmas', 'solicitud.tipoPermiso'])
            ->where('token', $token)
            ->firstOrFail();

        if ($firma->estatus === 'firmado') {
            return redirect()->route('permisos.firma.show', $token)->with('success', 'Este documento ya fue firmado.');
        }

        $request->validate([
            'firma_data' => ['required', 'string'],
            'acepto' => ['accepted'],
        ], [
            'firma_data.required' => 'Debes dibujar tu firma.',
            'acepto.accepted' => 'Debes aceptar la firma del documento.',
        ]);

        $firmaPath = $this->guardarFirmaBase64($request->firma_data, $firma);

        DB::transaction(function () use ($request, $firma, $firmaPath) {
            $firma->update([
                'estatus' => 'firmado',
                'firma_path' => $firmaPath,
                'firmado_at' => now(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $solicitud = $firma->solicitud->fresh(['firmas']);
            $pendientes = $solicitud->firmas()->where('estatus', 'pendiente')->count();

            if ($pendientes === 0) {
                $solicitud->update(['estatus' => 'firmado_completo']);
            } elseif ($solicitud->firmas()->where('tipo_firma', 'lider')->where('estatus', 'pendiente')->exists()) {
                $solicitud->update(['estatus' => 'pendiente_firma_lider']);
            } else {
                $solicitud->update(['estatus' => 'pendiente_firma_colaborador']);
            }
        });

        return redirect()->route('permisos.firma.show', $token)->with('success', 'Firma registrada correctamente.');
    }

    private function crearFirma(PermisoSolicitud $solicitud, Empleado $empleado, string $tipoFirma): PermisoFirma
    {
        return PermisoFirma::create([
            'permiso_solicitud_id' => $solicitud->id,
            'empleado_id' => $empleado->id,
            'tipo_firma' => $tipoFirma,
            'nombre' => $empleado->nombre,
            'correo' => $empleado->correo,
            'token' => Str::random(64),
            'estatus' => 'pendiente',
        ]);
    }

    private function enviarCorreosFirma(PermisoSolicitud $solicitud): void
    {
        foreach ($solicitud->firmas as $firma) {
            try {
                Mail::to($firma->correo)->send(new PermisoFirmaMail($solicitud, $firma));

                PermisoNotificacion::create([
                    'permiso_solicitud_id' => $solicitud->id,
                    'correo' => $firma->correo,
                    'tipo' => 'firma_' . $firma->tipo_firma,
                    'estatus' => 'enviado',
                    'enviado_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Error enviando correo de firma de permiso', [
                    'permiso_solicitud_id' => $solicitud->id,
                    'correo' => $firma->correo,
                    'error' => $e->getMessage(),
                ]);

                PermisoNotificacion::create([
                    'permiso_solicitud_id' => $solicitud->id,
                    'correo' => $firma->correo,
                    'tipo' => 'firma_' . $firma->tipo_firma,
                    'estatus' => 'error',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function guardarFirmaBase64(string $firmaData, PermisoFirma $firma): string
    {
        $firmaData = preg_replace('/^data:image\/png;base64,/', '', $firmaData);
        $firmaData = str_replace(' ', '+', $firmaData);
        $binary = base64_decode($firmaData);

        $path = 'firmas/permiso_' . $firma->permiso_solicitud_id . '_' . $firma->tipo_firma . '_' . $firma->id . '.png';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function calcularDiasHabiles(Carbon $inicio, Carbon $fin): float
    {
        $inhabiles = DiaInhabil::where('activo', true)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->pluck('fecha')
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
            ->toArray();

        $dias = 0;

        foreach (CarbonPeriod::create($inicio, $fin) as $dia) {
            if ($dia->isWeekend()) {
                continue;
            }

            if (in_array($dia->toDateString(), $inhabiles, true)) {
                continue;
            }

            $dias++;
        }

        return $dias;
    }
}
