<?php

namespace App\Http\Controllers;

use App\Models\PermisoSolicitud;
use App\Models\TipoPermiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPermisoController extends Controller
{
    public function index(Request $request)
    {
        $query = PermisoSolicitud::with(['tipoPermiso', 'empleado.area', 'lider', 'firmas'])
            ->latest();

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

        $solicitudes = $query->paginate(20)->withQueryString();
        $tipos = TipoPermiso::orderBy('nombre')->get();

        $stats = [
            'total' => PermisoSolicitud::count(),
            'pendientes' => PermisoSolicitud::whereIn('estatus', ['pendiente_firma_colaborador', 'pendiente_firma_lider', 'formato_pendiente'])->count(),
            'firmados' => PermisoSolicitud::where('estatus', 'firmado_completo')->count(),
            'recibidos' => PermisoSolicitud::where('estatus', 'formato_recibido')->count(),
        ];

        return view('admin.permisos.index', compact('solicitudes', 'tipos', 'stats'));
    }

    public function show(PermisoSolicitud $solicitud)
    {
        $solicitud->load(['tipoPermiso', 'empleado.area', 'lider', 'firmas', 'notificaciones', 'recibidoPor']);

        return view('admin.permisos.show', compact('solicitud'));
    }

    public function marcarRecibido(PermisoSolicitud $solicitud)
    {
        DB::transaction(function () use ($solicitud) {
            $solicitud->load(['tipoPermiso', 'empleado']);

            if (! $solicitud->formato_recibido && $solicitud->tipoPermiso?->descuenta_vacaciones) {
                $solicitud->empleado->decrement('vacaciones_pendientes', $solicitud->dias_solicitados);
                $solicitud->empleado->increment('vacaciones_usados', $solicitud->dias_solicitados);
            }

            $solicitud->update([
                'estatus' => 'formato_recibido',
                'formato_recibido' => true,
                'formato_recibido_at' => now(),
                'formato_recibido_por' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Formato marcado como recibido por RH.');
    }

    public function marcarPendiente(PermisoSolicitud $solicitud)
    {
        DB::transaction(function () use ($solicitud) {
            $solicitud->load(['tipoPermiso', 'empleado']);

            if ($solicitud->formato_recibido && $solicitud->tipoPermiso?->descuenta_vacaciones) {
                $solicitud->empleado->decrement('vacaciones_usados', $solicitud->dias_solicitados);
                $solicitud->empleado->increment('vacaciones_pendientes', $solicitud->dias_solicitados);
            }

            $solicitud->update([
                'estatus' => 'formato_pendiente',
                'formato_recibido' => false,
                'formato_recibido_at' => null,
                'formato_recibido_por' => null,
            ]);
        });

        return back()->with('success', 'Formato marcado como pendiente de recepción.');
    }

    public function observaciones(Request $request, PermisoSolicitud $solicitud)
    {
        $validated = $request->validate([
            'observaciones_rh' => ['required', 'string', 'max:3000'],
        ]);

        $solicitud->update([
            'estatus' => 'con_observaciones',
            'observaciones_rh' => $validated['observaciones_rh'],
        ]);

        return back()->with('success', 'Observaciones guardadas.');
    }

    public function cancelar(PermisoSolicitud $solicitud)
    {
        DB::transaction(function () use ($solicitud) {
            $solicitud->load(['tipoPermiso', 'empleado']);

            if ($solicitud->tipoPermiso?->descuenta_vacaciones) {
                if ($solicitud->formato_recibido) {
                    $solicitud->empleado->decrement('vacaciones_usados', $solicitud->dias_solicitados);
                } else {
                    $solicitud->empleado->decrement('vacaciones_pendientes', $solicitud->dias_solicitados);
                }
            }

            $solicitud->update([
                'estatus' => 'cancelado',
                'formato_recibido' => false,
            ]);
        });

        return back()->with('success', 'Solicitud cancelada y saldo liberado si aplicaba.');
    }
}
