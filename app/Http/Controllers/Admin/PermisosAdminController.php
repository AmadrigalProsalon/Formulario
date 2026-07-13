<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PermisoDocumentoFisicoMail;
use App\Models\Area;
use App\Models\PermisoHistorial;
use App\Models\PermisoSolicitud;
use App\Models\TipoPermiso;
use App\Services\Permisos\PermisoDocumentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PermisosAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PermisoSolicitud::with(['tipoPermiso', 'empleado.area', 'lider', 'area', 'diasSeleccionados'])
            ->latest();

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('tipo_permiso_id')) {
            $query->where('tipo_permiso_id', $request->tipo_permiso_id);
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->whereHas('empleado', function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%");
            });
        }

        return view('admin.permisos.index', [
            'solicitudes' => $query->paginate(20)->withQueryString(),
            'areas' => Area::orderBy('nombre')->get(),
            'tiposPermisos' => TipoPermiso::orderBy('nombre')->get(),
            'filters' => $request->only(['area_id', 'tipo_permiso_id', 'estatus', 'q']),
        ]);
    }

    public function calendario(Request $request)
    {
        $anio = (int) $request->input('anio', now()->year);
        $mes = (int) $request->input('mes', now()->month);

        $inicio = now()->setDate($anio, $mes, 1)->startOfDay();
        $fin = (clone $inicio)->endOfMonth();

        $query = PermisoSolicitud::with(['tipoPermiso', 'empleado.area', 'area'])
            ->whereDate('fecha_inicio', '<=', $fin->toDateString())
            ->whereDate('fecha_fin', '>=', $inicio->toDateString())
            ->whereNotIn('estatus', ['cancelado']);

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('tipo_permiso_id')) {
            $query->where('tipo_permiso_id', $request->tipo_permiso_id);
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $solicitudes = $query->orderBy('fecha_inicio')->get();

        return view('admin.permisos.calendario', [
            'solicitudes' => $solicitudes,
            'areas' => Area::orderBy('nombre')->get(),
            'tiposPermisos' => TipoPermiso::orderBy('nombre')->get(),
            'filters' => $request->only(['area_id', 'tipo_permiso_id', 'estatus', 'mes', 'anio']),
            'mes' => $mes,
            'anio' => $anio,
            'inicio' => $inicio,
            'fin' => $fin,
        ]);
    }

    public function show(PermisoSolicitud $permiso)
    {
        $permiso->load([
            'tipoPermiso',
            'empleado.area',
            'empleado.lider',
            'lider',
            'area',
            'recibidoPor',
            'archivoFirmadoPor',
            'historial.usuario',
            'diasSeleccionados',
        ]);

        $envios = DB::table('permiso_documento_envios')
            ->where('permiso_solicitud_id', $permiso->id)
            ->latest()
            ->get();

        return view('admin.permisos.show', compact('permiso', 'envios'));
    }

    public function marcarRecibido(Request $request, PermisoSolicitud $permiso)
    {
        $permiso->update([
            'estatus' => 'formato_recibido',
            'formato_recibido' => true,
            'formato_recibido_at' => now(),
            'formato_recibido_por' => auth()->id(),
            'observaciones_rh' => $request->input('observaciones_rh'),
        ]);

        $this->registrarHistorial($permiso, 'formato_recibido', 'RH marcó el formato como recibido.');

        return back()->with('success', 'Formato marcado como recibido. Si es vacaciones, desde este momento cuenta como usado.');
    }

    public function marcarPendiente(PermisoSolicitud $permiso)
    {
        $permiso->update([
            'estatus' => 'formato_pendiente',
            'formato_recibido' => false,
            'formato_recibido_at' => null,
            'formato_recibido_por' => null,
        ]);

        $this->registrarHistorial($permiso, 'formato_pendiente', 'RH marcó el formato como pendiente.');

        return back()->with('success', 'Formato marcado como pendiente. No descuenta días.');
    }

    public function marcarObservaciones(Request $request, PermisoSolicitud $permiso)
    {
        $request->validate([
            'observaciones_rh' => ['required', 'string', 'max:3000'],
        ]);

        $permiso->update([
            'estatus' => 'con_observaciones',
            'formato_recibido' => false,
            'formato_recibido_at' => null,
            'formato_recibido_por' => null,
            'observaciones_rh' => $request->observaciones_rh,
        ]);

        $this->registrarHistorial($permiso, 'con_observaciones', $request->observaciones_rh);

        return back()->with('success', 'Solicitud marcada con observaciones. No descuenta días.');
    }

    public function cancelar(Request $request, PermisoSolicitud $permiso)
    {
        $permiso->update([
            'estatus' => 'cancelado',
            'formato_recibido' => false,
            'formato_recibido_at' => null,
            'formato_recibido_por' => null,
            'cancelado_at' => now(),
            'cancelado_por' => auth()->id(),
            'observaciones_rh' => $request->input('observaciones_rh', $permiso->observaciones_rh),
        ]);

        $this->registrarHistorial($permiso, 'cancelado', $request->input('observaciones_rh', 'Solicitud cancelada por RH.'));

        return back()->with('success', 'Solicitud cancelada. Los días no se descuentan.');
    }

    public function descargar(PermisoSolicitud $permiso, PermisoDocumentoService $documentoService): BinaryFileResponse
    {
        if (! $permiso->documento_path) {
            $documentoService->generarDocumento($permiso);
            $permiso->refresh();
        }

        return response()->download($documentoService->absolutePath($permiso->documento_path));
    }

    public function reenviar(PermisoSolicitud $permiso, PermisoDocumentoService $documentoService)
    {
        if (! $permiso->documento_path) {
            $documentoService->generarDocumento($permiso);
            $permiso->refresh();
        }

        $permiso->load(['empleado', 'lider', 'area', 'tipoPermiso']);
        $path = $documentoService->absolutePath($permiso->documento_path);
        $rhEmail = config('permisos.rh_email');

        $destinos = collect([
            ['correo' => $permiso->empleado?->correo, 'tipo' => 'colaborador'],
            ['correo' => $permiso->lider?->correo, 'tipo' => 'lider'],
            ['correo' => $rhEmail, 'tipo' => 'rh'],
        ])->filter(fn ($d) => ! empty($d['correo']));

        foreach ($destinos as $destino) {
            Mail::to($destino['correo'])->send(new PermisoDocumentoFisicoMail($permiso, $path, $destino['tipo']));
        }

        $permiso->update([
            'documento_enviado_at' => now(),
            'estatus' => $permiso->estatus === 'formato_generado' ? 'formato_enviado' : $permiso->estatus,
        ]);

        $this->registrarHistorial($permiso, 'documento_reenviado', 'Documento reenviado al colaborador, líder y RH.');

        return back()->with('success', 'Documento reenviado al colaborador, líder y RH.');
    }

    public function subirFormatoFirmado(Request $request, PermisoSolicitud $permiso)
    {
        $request->validate([
            'archivo_firmado' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:20480'],
        ]);

        $archivo = $request->file('archivo_firmado');
        $path = $archivo->store("permisos/firmados/solicitud_{$permiso->id}", 'public');

        $permiso->update([
            'archivo_firmado_path' => $path,
            'archivo_firmado_original' => $archivo->getClientOriginalName(),
            'archivo_firmado_at' => now(),
            'archivo_firmado_por' => auth()->id(),
        ]);

        $this->registrarHistorial($permiso, 'archivo_firmado_subido', 'RH subió el formato firmado escaneado.', [
            'archivo' => $archivo->getClientOriginalName(),
        ]);

        return back()->with('success', 'Formato firmado subido correctamente.');
    }

    public function descargarFormatoFirmado(PermisoSolicitud $permiso): BinaryFileResponse
    {
        abort_unless($permiso->archivo_firmado_path && Storage::disk('public')->exists($permiso->archivo_firmado_path), 404);

        return response()->download(
            Storage::disk('public')->path($permiso->archivo_firmado_path),
            $permiso->archivo_firmado_original ?: basename($permiso->archivo_firmado_path)
        );
    }

    private function registrarHistorial(PermisoSolicitud $permiso, string $accion, ?string $descripcion = null, array $metadata = []): void
    {
        PermisoHistorial::create([
            'permiso_solicitud_id' => $permiso->id,
            'user_id' => auth()->id(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'metadata' => $metadata ?: null,
        ]);
    }
}
