<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Models\PermisoSolicitud;
use App\Services\Permisos\PermisoDocumentoService;
use App\Services\Permisos\PermisoDocumentoWorkflowService;
use Illuminate\Support\Facades\Storage;

class PermisoDocumentoController extends Controller
{
    public function descargarInicial(PermisoSolicitud $solicitud, PermisoDocumentoService $service)
    {
        $path = $solicitud->documento_inicial_path ?: $service->generarDocumento($solicitud, false);

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Documento no encontrado.');
        }

        return Storage::disk('public')->download($path, 'formato_permiso_' . $solicitud->id . '.docx');
    }

    public function descargarFirmado(PermisoSolicitud $solicitud, PermisoDocumentoService $service)
    {
        $path = $solicitud->documento_firmado_path ?: $service->generarDocumento($solicitud, true);

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Documento firmado no encontrado.');
        }

        return Storage::disk('public')->download($path, 'formato_permiso_firmado_' . $solicitud->id . '.docx');
    }

    public function reenviarInicial(PermisoSolicitud $solicitud, PermisoDocumentoWorkflowService $workflow)
    {
        $workflow->enviarDocumentoInicial($solicitud);

        return back()->with('success', 'Formato inicial reenviado correctamente.');
    }

    public function reenviarFirmadoRh(PermisoSolicitud $solicitud, PermisoDocumentoWorkflowService $workflow)
    {
        $workflow->reenviarDocumentoFirmadoRh($solicitud);

        return back()->with('success', 'Formato firmado reenviado a RH correctamente.');
    }
}
