<?php

namespace App\Services\Permisos;

use App\Mail\Permisos\PermisoDocumentoFirmadoRhMail;
use App\Mail\Permisos\PermisoDocumentoInicialMail;
use App\Models\PermisoSolicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PermisoDocumentoWorkflowService
{
    public function __construct(
        private readonly PermisoDocumentoService $documentoService
    ) {
    }

    public function enviarDocumentoInicial(PermisoSolicitud $solicitud): void
    {
        $solicitud->loadMissing(['empleado', 'area', 'lider', 'tipoPermiso']);

        $path = $solicitud->documento_inicial_path ?: $this->documentoService->generarDocumento($solicitud, false);

        $this->actualizarSolicitud($solicitud, [
            'documento_inicial_path' => $path,
            'documento_inicial_enviado_at' => now(),
        ]);

        $empleadoCorreo = $this->correoEmpleado($solicitud);
        $liderCorreo = $this->correoLider($solicitud);
        $rhCorreo = config('permisos.rh_email');

        if (! $empleadoCorreo && ! $liderCorreo && ! $rhCorreo) {
            return;
        }

        $mail = new PermisoDocumentoInicialMail($solicitud, $path);

        $to = $empleadoCorreo ?: $rhCorreo;
        $cc = collect([$liderCorreo, $rhCorreo])
            ->filter()
            ->unique()
            ->values()
            ->all();

        Mail::to($to)->cc($cc)->send($mail);
    }

    public function procesarFirmasCompletas(PermisoSolicitud $solicitud): bool
    {
        $solicitud->loadMissing(['empleado', 'area', 'lider', 'tipoPermiso']);

        if (! $this->firmasCompletas($solicitud)) {
            return false;
        }

        if ($solicitud->documento_firmado_path && $solicitud->documento_firmado_enviado_rh_at) {
            return true;
        }

        $path = $this->documentoService->generarDocumento($solicitud, true);

        $this->actualizarSolicitud($solicitud, [
            'documento_firmado_path' => $path,
            'documento_firmado_enviado_rh_at' => now(),
            'estatus' => 'firmado_completo',
        ]);

        $rhCorreo = config('permisos.rh_email');

        if ($rhCorreo) {
            Mail::to($rhCorreo)->send(new PermisoDocumentoFirmadoRhMail($solicitud->fresh(), $path));
        }

        return true;
    }

    public function reenviarDocumentoFirmadoRh(PermisoSolicitud $solicitud): void
    {
        $solicitud->loadMissing(['empleado', 'area', 'lider', 'tipoPermiso']);

        $path = $solicitud->documento_firmado_path ?: $this->documentoService->generarDocumento($solicitud, true);

        $this->actualizarSolicitud($solicitud, [
            'documento_firmado_path' => $path,
            'documento_firmado_enviado_rh_at' => now(),
        ]);

        $rhCorreo = config('permisos.rh_email');

        if ($rhCorreo) {
            Mail::to($rhCorreo)->send(new PermisoDocumentoFirmadoRhMail($solicitud, $path));
        }
    }

    public function firmasCompletas(PermisoSolicitud $solicitud): bool
    {
        if (! Schema::hasTable('permiso_firmas')) {
            return false;
        }

        $firmasRequeridas = config('permisos.firmas_requeridas', ['colaborador', 'lider']);
        $estatusFirmado = config('permisos.estatus_firma_firmado', 'firmado');

        foreach ($firmasRequeridas as $tipoFirma) {
            $existe = DB::table('permiso_firmas')
                ->where('permiso_solicitud_id', $solicitud->id)
                ->where('tipo_firma', $tipoFirma)
                ->where('estatus', $estatusFirmado)
                ->exists();

            if (! $existe) {
                return false;
            }
        }

        return true;
    }

    private function actualizarSolicitud(PermisoSolicitud $solicitud, array $data): void
    {
        $table = $solicitud->getTable();

        $data = collect($data)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->toArray();

        if (! empty($data)) {
            $solicitud->update($data);
        }
    }

    private function correoEmpleado(PermisoSolicitud $solicitud): ?string
    {
        return $solicitud->empleado->correo
            ?? $solicitud->correo_colaborador
            ?? null;
    }

    private function correoLider(PermisoSolicitud $solicitud): ?string
    {
        return $solicitud->lider->correo
            ?? $solicitud->correo_lider
            ?? null;
    }
}
