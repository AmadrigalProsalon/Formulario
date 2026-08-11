<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\PermisoSolicitud;
use App\Services\Permisos\PermisoSaldoService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PermisosEmpleadosController extends Controller
{
    private string $motivoImportado = 'Vacación histórica importada desde Excel/CSV de saldos.';

    public function index(Request $request)
    {
        $empleados = $this->empleadosQuery($request)
            ->paginate(25)
            ->withQueryString();
        $saldoService = app(PermisoSaldoService::class);
        $empleados->getCollection()->each(function (Empleado $empleado) use ($saldoService) {
            $empleado->setAttribute('saldo_calculado', $saldoService->resumen($empleado));
        });

        return view('admin.permisos-catalogos.empleados.index', [
            'empleados' => $empleados,
            'areas' => Area::orderBy('nombre')->get(),
            'lideres' => Empleado::where('es_lider', true)->orderBy('nombre')->get(),
            'filters' => $request->only(['area_id', 'activo', 'q']),
        ]);
    }


    public function export(Request $request): StreamedResponse
    {
        // Los registros auxiliares creados para líderes usan números como
        // LIDER-XXXXXXXX. No son colaboradores y no deben salir en el Excel.
        $empleados = $this->empleadosQuery($request)
            ->where(function ($query) {
                $query->whereNull('numero_empleado')
                    ->orWhereRaw('UPPER(numero_empleado) NOT LIKE ?', ['LIDER%']);
            })
            ->get();
        $saldoService = app(PermisoSaldoService::class);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Formularios RH')
            ->setTitle('Empleados, saldos e historial de vacaciones')
            ->setSubject('Exportación de empleados y vacaciones')
            ->setDescription('Saldos actuales e historial de vacaciones generado desde Formularios RH.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Concentrado');

        $headers = [
            'Número de empleado',
            'Nombre',
            'CURP',
            'RFC',
            'Correo',
            'Departamento',
            'Puesto',
            'Líder',
            'Fecha de ingreso',
            'Estado',
            'Fecha de corte',
            'Base Excel',
            'Saldo año anterior',
            'Saldo año actual',
            'Proporcional generado',
            'Días tomados',
            'Días apartados o pendientes',
            'Días disponibles',
            'Vencimiento saldo anterior',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $fila = 2;

        foreach ($empleados as $empleado) {
            $saldo = $saldoService->resumen($empleado);

            $sheet->fromArray([
                $empleado->numero_empleado,
                $empleado->nombre,
                $empleado->curp,
                $empleado->rfc,
                $empleado->correo,
                $empleado->area?->nombre,
                $empleado->puesto,
                $empleado->lider?->nombre,
                $empleado->fecha_ingreso?->format('d/m/Y'),
                $empleado->activo ? 'Activo' : 'Inactivo',
                $saldo['fecha_corte_formato'] ?? null,
                (float) ($saldo['dias_base_excel'] ?? 0),
                (float) ($saldo['saldo_anio_anterior'] ?? 0),
                (float) ($saldo['saldo_anio_actual'] ?? 0),
                (float) ($saldo['proporcional_generado'] ?? 0),
                (float) ($saldo['dias_tomados'] ?? 0),
                (float) ($saldo['dias_apartados'] ?? 0),
                (float) ($saldo['dias_disponibles'] ?? 0),
                $saldo['fecha_vencimiento'] ?? null,
            ], null, "A{$fila}");

            $fila++;
        }

        if ($fila > 2) {
            $sheet->getStyle('L2:R' . ($fila - 1))
                ->getNumberFormat()
                ->setFormatCode('0.00');
        }

        $this->estilizarHoja($sheet, 'S', max(1, $fila - 1));

        $historialSheet = $spreadsheet->createSheet();
        $historialSheet->setTitle('Historial vacaciones');

        $historialHeaders = [
            'Folio',
            'Número de empleado',
            'Colaborador',
            'Departamento',
            'Tipo',
            'Origen',
            'Estatus',
            'Fecha inicial',
            'Fecha final',
            'Días',
            'Fechas seleccionadas',
            'Fecha de registro',
            'Fecha de aprobación',
            'Fecha de rechazo',
            'Motivo / referencia',
            'Observaciones RH',
        ];

        $historialSheet->fromArray($historialHeaders, null, 'A1');

        $empleadosIds = $empleados->pluck('id');
        $historial = PermisoSolicitud::with([
                'empleado.area',
                'area',
                'tipoPermiso',
                'diasSeleccionados',
            ])
            ->whereIn('empleado_id', $empleadosIds->all())
            ->whereHas('tipoPermiso', function ($query) {
                $query->where('descuenta_vacaciones', true)
                    ->orWhere('slug', 'vacaciones');
            })
            ->orderBy('fecha_inicio')
            ->orderBy('id')
            ->get();

        $filaHistorial = 2;

        foreach ($historial as $solicitud) {
            $fechasSeleccionadas = $solicitud->diasSeleccionados
                ->map(fn ($dia) => $dia->fecha?->format('d/m/Y'))
                ->filter()
                ->implode(', ');

            $historialSheet->fromArray([
                $solicitud->id,
                $solicitud->empleado?->numero_empleado,
                $solicitud->empleado?->nombre,
                $solicitud->area?->nombre ?? $solicitud->empleado?->area?->nombre,
                $solicitud->tipoPermiso?->nombre,
                $solicitud->esHistorica() ? 'Registro histórico' : 'Solicitud',
                $solicitud->etiquetaEstatus(),
                $solicitud->fecha_inicio?->format('d/m/Y'),
                $solicitud->fecha_fin?->format('d/m/Y'),
                (float) $solicitud->dias_solicitados,
                $fechasSeleccionadas,
                $solicitud->created_at?->format('d/m/Y H:i'),
                $solicitud->formato_recibido_at?->format('d/m/Y H:i'),
                $solicitud->rechazado_at?->format('d/m/Y H:i'),
                $solicitud->motivo,
                $solicitud->observaciones_rh,
            ], null, "A{$filaHistorial}");

            $filaHistorial++;
        }

        if ($filaHistorial > 2) {
            $historialSheet->getStyle('J2:J' . ($filaHistorial - 1))
                ->getNumberFormat()
                ->setFormatCode('0.00');
        }

        $this->estilizarHoja($historialSheet, 'P', max(1, $filaHistorial - 1));
        $historialSheet->getColumnDimension('K')->setWidth(42);
        $historialSheet->getColumnDimension('O')->setWidth(48);
        $historialSheet->getColumnDimension('P')->setWidth(48);
        $historialSheet->getStyle('K2:P' . max(2, $filaHistorial - 1))
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);

        $spreadsheet->setActiveSheetIndex(0);
        $nombreArchivo = 'empleados_saldos_historico_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'lider_id' => ['nullable', 'exists:empleados,id'],
            'numero_empleado' => ['nullable', 'string', 'max:50', Rule::unique('empleados', 'numero_empleado')],
            'curp' => ['nullable', 'string', 'max:18'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'vacaciones_saldo_anterior_base' => ['nullable', 'numeric', 'min:0'],
            'vacaciones_saldo_actual_base' => ['nullable', 'numeric', 'min:0'],
            'vacaciones_fecha_corte' => ['nullable', 'date', 'before_or_equal:today'],
            'es_lider' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
            'dias_laborales' => ['nullable', 'array'],
            'dias_laborales.*' => ['integer', 'between:1,7'],
        ], [
            'numero_empleado.unique' => 'Ese número de empleado ya está registrado.',
            'nombre.required' => 'El nombre del empleado es obligatorio.',
        ]);

        $validated['numero_empleado'] = filled($validated['numero_empleado'] ?? null)
            ? trim($validated['numero_empleado'])
            : null;
        $validated['correo'] = filled($validated['correo'] ?? null)
            ? mb_strtolower(trim($validated['correo']))
            : null;
        $validated['curp'] = $this->normalizarClave($validated['curp'] ?? null);
        $validated['rfc'] = $this->normalizarClave($validated['rfc'] ?? null);
        $validated['es_lider'] = $request->boolean('es_lider');
        $validated['activo'] = $request->boolean('activo', true);
        $validated['dias_laborales'] = $request->filled('dias_laborales')
            ? array_values(array_unique(array_map('intval', $request->input('dias_laborales', []))))
            : null;

        $fechaCorte = Carbon::parse($validated['vacaciones_fecha_corte'] ?? now()->toDateString());
        $saldoAnterior = round((float) ($validated['vacaciones_saldo_anterior_base'] ?? 0), 4);
        $saldoActual = round((float) ($validated['vacaciones_saldo_actual_base'] ?? 0), 4);

        $validated['vacaciones_saldo_anterior_base'] = $saldoAnterior;
        $validated['vacaciones_saldo_actual_base'] = $saldoActual;
        $validated['vacaciones_ganadas_base'] = round($saldoAnterior + $saldoActual, 4);
        $validated['vacaciones_fecha_corte'] = $fechaCorte->toDateString();
        $validated['vacaciones_anio_base'] = $fechaCorte->year;
        $validated['vacaciones_fecha_vencimiento'] = Carbon::create($fechaCorte->year, 4, 30)->toDateString();
        $validated['vacaciones_ajuste'] = 0;
        $validated['vacaciones_usados'] = 0;
        $validated['vacaciones_pendientes'] = 0;

        $empleado = Empleado::create($validated);

        return redirect()
            ->route('admin.permisos.empleados.index', ['q' => $empleado->numero_empleado ?: $empleado->nombre])
            ->with('success', 'Empleado agregado manualmente. Ya puede usarse en las solicitudes.');
    }

    public function importForm()
    {
        return view('admin.permisos-catalogos.empleados.importar');
    }

    public function import(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt,xlsx,xls'],
            'fecha_corte' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $this->ensureImportSchema();
        $fechaCorte = Carbon::parse($request->input('fecha_corte'))->startOfDay();

        $rows = $this->leerArchivo($request->file('archivo'));

        if (count($rows) < 2) {
            return back()->with('error', 'El archivo no tiene información suficiente para importar.');
        }

        [$headerRowIndex, $headers] = $this->detectHeaders($rows);

        if (empty($headers)) {
            return back()->with('error', 'No pude detectar encabezados como CLAVE, NOMBRE, DEPARTAMENTO, PUESTO o FECHA INGRESO.');
        }

        // Esta es la columna oficial indicada por RH. normalizeHeader() elimina
        // acentos, espacios adicionales y el espacio final que trae el CSV.
        $columnaSaldoOficial = $this->normalizeHeader(
            'DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL'
        );

        if (! array_key_exists($columnaSaldoOficial, $headers)) {
            return back()->with('error',
                'No se encontró la columna oficial: DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL.'
            );
        }

        $creados = 0;
        $actualizados = 0;
        $omitidos = 0;
        $lideresCreados = 0;
        $areasCreadas = 0;
        $vacacionesCreadas = 0;
        $vacacionesOmitidas = 0;
        $vacacionesReemplazadas = 0;
        $errores = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex <= $headerRowIndex) {
                    continue;
                }

                $numeroEmpleado = $this->limpiarTexto($this->value($row, $headers, [
                    'CLAVE',
                    'NUMERO_EMPLEADO',
                    'NO_EMPLEADO',
                    'N_EMPLEADO',
                    'ID_EMPLEADO',
                    'CODIGO',
                    'CODIGO_EMPLEADO',
                ]));

                $nombre = $this->limpiarTexto($this->value($row, $headers, [
                    'NOMBRE',
                    'NOMBRE_COMPLETO',
                    'EMPLEADO',
                    'COLABORADOR',
                    'TRABAJADOR',
                ]));

                if (! $numeroEmpleado && ! $nombre) {
                    $omitidos++;
                    continue;
                }

                if (! $nombre) {
                    $errores[] = 'Fila ' . ($rowIndex + 1) . ': se omitió porque no tiene nombre.';
                    $omitidos++;
                    continue;
                }

                $departamento = $this->limpiarTexto($this->value($row, $headers, [
                    'DEPARTAMENTO',
                    'AREA',
                    'AREA_DEPARTAMENTO',
                    'DEPTO',
                    'SUCURSAL',
                ]));

                $puesto = $this->limpiarTexto($this->value($row, $headers, [
                    'PUESTO',
                    'NOMBRE_PUESTO',
                    'CARGO',
                    'POSICION',
                    'POSICIÓN',
                ]));

                $jefeDirecto = $this->limpiarTexto($this->value($row, $headers, [
                    'JEFE_DIRECTO',
                    'JEFE',
                    'LIDER',
                    'LÍDER',
                    'LIDER_DIRECTO',
                    'RESPONSABLE',
                ]));

                $fechaIngreso = $this->fecha($this->value($row, $headers, [
                    'FECHA_INGRESO',
                    'FECHA_DE_INGRESO',
                    'INGRESO',
                    'FECHA_ALTA',
                    'FECHA_ENTRADA',
                ]));

                // Correo propio del colaborador. El archivo oficial usa
                // "Direccion de Correo Colaborador"; se permiten correos repetidos
                // porque en RH algunos colaboradores comparten una cuenta operativa.
                $correo = $this->limpiarCorreo($this->value($row, $headers, [
                    'DIRECCION_DE_CORREO_COLABORADOR',
                    'DIRECCIÓN_DE_CORREO_COLABORADOR',
                    'CORREO_COLABORADOR',
                    'EMAIL_COLABORADOR',
                    'CORREO',
                    'EMAIL',
                    'CORREO_ELECTRONICO',
                    'CORREO_ELECTRÓNICO',
                ]));

                // El correo del jefe pertenece al registro del líder, no se copia
                // al colaborador. Puede repetirse en muchas filas y eso es normal.
                $correoJefe = $this->limpiarCorreo($this->value($row, $headers, [
                    'DIRECCION_DE_CORREO_JEFE',
                    'DIRECCIÓN_DE_CORREO_JEFE',
                    'CORREO_JEFE',
                    'EMAIL_JEFE',
                    'CORREO_LIDER',
                    'EMAIL_LIDER',
                ]));

                $curp = $this->normalizarClave($this->value($row, $headers, ['CURP']));
                $rfc = $this->normalizarClave($this->value($row, $headers, ['RFC']));

                $diasGanadosAlDia = $this->decimal($this->value($row, $headers, [
                    'DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL',
                ]));

                $fechasVacaciones = $this->extraerFechasVacaciones($row, $headers);
                $diasHistoricos = count($fechasVacaciones);

                $areaId = null;
                if ($departamento) {
                    [$areaId, $areaCreada] = $this->obtenerOCrearArea($departamento);
                    if ($areaCreada) {
                        $areasCreadas++;
                    }
                }

                $liderId = null;
                if ($jefeDirecto) {
                    [$liderId, $liderCreado] = $this->obtenerOCrearLider($jefeDirecto, $areaId, $correoJefe);
                    if ($liderCreado) {
                        $lideresCreados++;
                    }
                }

                $empleado = $this->buscarEmpleado($numeroEmpleado, $curp, $rfc, $nombre);
                $esNuevo = ! $empleado;

                // El valor de esta columna ya es el saldo oficial al corte del archivo.
                // No se restan nuevamente vacaciones históricas porque eso duplicaría el descuento.
                $baseGanadaExcel = round((float) ($diasGanadosAlDia ?? 0), 4);
                if ($diasGanadosAlDia === null) {
                    $errores[] = 'Fila ' . ($rowIndex + 1) . ': la columna oficial está vacía o contiene un valor no numérico; se asignó 0.';
                }
                $saldoSnapshot = round(max(0, $baseGanadaExcel), 4);

                // Separamos el saldo oficial en dos bolsas. Entre enero y abril,
                // la parte que excede el proporcional del año actual se considera
                // saldo del año anterior y vence el 30 de abril. A partir de mayo,
                // todo el saldo oficial vigente pertenece al año actual.
                $proporcionalAnioActualAlCorte = $fechaIngreso
                    ? app(\App\Services\Permisos\PermisoSaldoService::class)
                        ->proporcionalGeneradoEnAnio(Carbon::parse($fechaIngreso), $fechaCorte)
                    : 0.0;

                if ($fechaCorte->month <= 4) {
                    $saldoActualBase = round(min($saldoSnapshot, $proporcionalAnioActualAlCorte), 4);
                    $saldoAnteriorBase = round(max(0, $saldoSnapshot - $saldoActualBase), 4);
                } else {
                    $saldoAnteriorBase = 0.0;
                    $saldoActualBase = $saldoSnapshot;
                }

                $data = [
                    'area_id' => $areaId,
                    'lider_id' => $liderId,
                    'numero_empleado' => $numeroEmpleado,
                    'curp' => $curp,
                    'rfc' => $rfc,
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'puesto' => $puesto,
                    'fecha_ingreso' => $fechaIngreso,
                    'es_lider' => false,
                    'activo' => true,
                    'vacaciones_ajuste' => 0,
                    'vacaciones_usados' => $diasHistoricos,
                    'vacaciones_pendientes' => $saldoSnapshot,
                    'vacaciones_ganadas_base' => $baseGanadaExcel,
                    'vacaciones_saldo_anterior_base' => $saldoAnteriorBase,
                    'vacaciones_saldo_actual_base' => $saldoActualBase,
                    'vacaciones_anio_base' => $fechaCorte->year,
                    'vacaciones_fecha_vencimiento' => Carbon::create($fechaCorte->year, 4, 30)->toDateString(),
                    'vacaciones_fecha_corte' => $fechaCorte->toDateString(),
                    'dias_vacaciones' => $baseGanadaExcel,
                    'dias_vacaciones_usados' => $diasHistoricos,
                    'departamento' => $departamento,
                    'updated_at' => now(),
                ];

                $data = $this->filtrarColumnas('empleados', $data);

                if ($empleado) {
                    DB::table('empleados')->where('id', $empleado->id)->update($data);
                    $empleadoId = $empleado->id;
                    $actualizados++;
                } else {
                    if (Schema::hasColumn('empleados', 'created_at')) {
                        $data['created_at'] = now();
                    }

                    $empleadoId = DB::table('empleados')->insertGetId($data);
                    $creados++;
                }

                $vacacionesReemplazadas += $this->eliminarVacacionesHistoricasImportadas($empleadoId);

                foreach ($fechasVacaciones as $fechaVacacion) {
                    $resultado = $this->guardarVacacionHistorica($empleadoId, $areaId, $liderId, $fechaVacacion);

                    if ($resultado === 'creada') {
                        $vacacionesCreadas++;
                    } else {
                        $vacacionesOmitidas++;
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }

        return back()->with('resultado_importacion', [
            'creados' => $creados,
            'actualizados' => $actualizados,
            'omitidos' => $omitidos,
            'areas_creadas' => $areasCreadas,
            'lideres_creados' => $lideresCreados,
            'vacaciones_creadas' => $vacacionesCreadas,
            'vacaciones_omitidas' => $vacacionesOmitidas,
            'vacaciones_reemplazadas' => $vacacionesReemplazadas,
            'errores' => $errores,
        ]);
    }

    public function update(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'lider_id' => ['nullable', 'exists:empleados,id'],
            'numero_empleado' => ['nullable', 'string', 'max:50'],
            'curp' => ['nullable', 'string', 'max:18'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'vacaciones_ajuste' => ['nullable', 'numeric'],
            'vacaciones_ganadas_base' => ['nullable', 'numeric', 'min:0'],
            'vacaciones_saldo_anterior_base' => ['nullable', 'numeric', 'min:0'],
            'vacaciones_saldo_actual_base' => ['nullable', 'numeric', 'min:0'],
            'vacaciones_fecha_corte' => ['nullable', 'date', 'before_or_equal:today'],
            'es_lider' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
            'dias_laborales' => ['nullable', 'array'],
            'dias_laborales.*' => ['integer', 'between:1,7'],
        ]);

        $validated['curp'] = $this->normalizarClave($validated['curp'] ?? null);
        $validated['rfc'] = $this->normalizarClave($validated['rfc'] ?? null);
        $validated['vacaciones_ajuste'] = $validated['vacaciones_ajuste'] ?? 0;
        $validated['es_lider'] = $request->boolean('es_lider');
        $validated['activo'] = $request->boolean('activo');
        $validated['dias_laborales'] = $request->filled('dias_laborales')
            ? array_values(array_unique(array_map('intval', $request->input('dias_laborales', []))))
            : null;

        $empleado->update($validated);

        return back()->with('success', 'Empleado actualizado correctamente.');
    }


    public function destroy(Empleado $empleado)
    {
        $nombre = $empleado->nombre;
        $empleadoId = $empleado->id;

        // Eliminamos los archivos asociados antes de borrar sus registros.
        if (Schema::hasTable('permisos_solicitudes')) {
            PermisoSolicitud::where('empleado_id', $empleadoId)
                ->get(['id', 'documento_path', 'archivo_firmado_path'])
                ->each(function (PermisoSolicitud $solicitud) {
                    if ($solicitud->documento_path) {
                        Storage::disk(config('permisos.documentos_disk', 'public'))
                            ->delete($solicitud->documento_path);
                    }

                    if ($solicitud->archivo_firmado_path) {
                        Storage::disk('public')->delete($solicitud->archivo_firmado_path);
                    }

                    Storage::disk('public')
                        ->deleteDirectory("permisos/firmados/solicitud_{$solicitud->id}");
                });
        }

        DB::transaction(function () use ($empleado, $empleadoId) {
            // Si era líder, sus colaboradores quedan disponibles para asignar
            // otro líder en lugar de impedir la eliminación.
            Empleado::where('lider_id', $empleadoId)->update(['lider_id' => null]);

            if (Schema::hasTable('permisos_solicitudes')) {
                $solicitudIds = DB::table('permisos_solicitudes')
                    ->where('empleado_id', $empleadoId)
                    ->pluck('id');

                foreach ([
                    'permiso_solicitud_dias',
                    'permiso_documento_envios',
                    'permisos_historial',
                    'permiso_notificaciones',
                    'permiso_firmas',
                ] as $tablaDetalle) {
                    if (Schema::hasTable($tablaDetalle)
                        && Schema::hasColumn($tablaDetalle, 'permiso_solicitud_id')
                        && $solicitudIds->isNotEmpty()) {
                        DB::table($tablaDetalle)
                            ->whereIn('permiso_solicitud_id', $solicitudIds->all())
                            ->delete();
                    }
                }

                if (Schema::hasColumn('permisos_solicitudes', 'lider_id')) {
                    DB::table('permisos_solicitudes')
                        ->where('lider_id', $empleadoId)
                        ->update(['lider_id' => null]);
                }

                DB::table('permisos_solicitudes')
                    ->where('empleado_id', $empleadoId)
                    ->delete();
            }

            if (Schema::hasTable('permiso_firmas') && Schema::hasColumn('permiso_firmas', 'empleado_id')) {
                DB::table('permiso_firmas')
                    ->where('empleado_id', $empleadoId)
                    ->update(['empleado_id' => null]);
            }

            foreach (['vacaciones_solicitudes', 'vacaciones_ajustes'] as $tabla) {
                if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'empleado_id')) {
                    DB::table($tabla)->where('empleado_id', $empleadoId)->delete();
                }
            }

            $empleado->delete();
        });

        return redirect()
            ->route('admin.permisos.empleados.index')
            ->with('success', "Empleado {$nombre} eliminado definitivamente junto con sus solicitudes e históricos.");
    }


    private function empleadosQuery(Request $request)
    {
        $query = Empleado::with(['area', 'lider'])->orderBy('nombre');

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $normalizado = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $q));

            $query->where(function ($sub) use ($q, $normalizado) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('correo', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%")
                    ->orWhere('curp', 'like', "%{$normalizado}%")
                    ->orWhere('rfc', 'like', "%{$normalizado}%")
                    ->orWhere('puesto', 'like', "%{$q}%");
            });
        }

        return $query;
    }

    private function estilizarHoja($sheet, string $ultimaColumna, int $ultimaFila): void
    {
        $rangoEncabezado = "A1:{$ultimaColumna}1";
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($rangoEncabezado);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->getStyle($rangoEncabezado)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '312E81'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        if ($ultimaFila > 1) {
            $sheet->getStyle("A2:{$ultimaColumna}{$ultimaFila}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP);
        }

        foreach (range('A', $ultimaColumna) as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }
    }

    private function ensureImportSchema(): void
    {
        if (! Schema::hasTable('areas')) {
            Schema::create('areas', function ($table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('empleados') || ! Schema::hasTable('tipos_permisos') || ! Schema::hasTable('permisos_solicitudes')) {
            return;
        }

        $this->obtenerTipoVacacionesId();
    }

    private function leerArchivo($archivo): array
    {
        $extension = strtolower($archivo->getClientOriginalExtension());
        $path = $archivo->getRealPath();

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->leerCsv($path);
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);

        return array_values(array_map(fn ($row) => array_values($row), $rawRows));
    }

    private function leerCsv(string $path): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return [];
        }

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
        }

        $firstLine = strtok($content, "\n") ?: '';
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_values($row);
        }

        fclose($handle);

        return $rows;
    }

    private function detectHeaders(array $rows): array
    {
        $bestRow = 0;
        $bestHeaders = [];
        $bestScore = 0;

        foreach ($rows as $index => $row) {
            if ($index > 15) {
                break;
            }

            $headers = [];

            foreach ($row as $columnIndex => $value) {
                $normalized = $this->normalizeHeader($value);

                if ($normalized !== '') {
                    $headers[$normalized] = $columnIndex;
                }
            }

            $score = 0;
            foreach (['CLAVE', 'NOMBRE', 'DEPARTAMENTO', 'PUESTO', 'FECHA_INGRESO'] as $required) {
                if (isset($headers[$required])) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRow = $index;
                $bestHeaders = $headers;
            }
        }

        return [$bestRow, $bestHeaders];
    }

    private function value(array $row, array $headers, array $names)
    {
        foreach ($names as $name) {
            $key = $this->normalizeHeader($name);

            if (isset($headers[$key])) {
                $columnIndex = $headers[$key];
                $value = $row[$columnIndex] ?? null;

                if ($value !== null && trim((string) $value) !== '') {
                    return trim((string) $value);
                }
            }
        }

        return null;
    }

    private function extraerFechasVacaciones(array $row, array $headers): array
    {
        $columnasConEncabezado = array_values($headers);
        $fechas = [];

        foreach ($row as $columnIndex => $value) {
            if (in_array($columnIndex, $columnasConEncabezado, true)) {
                continue;
            }

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            $posiblesValores = preg_split('/[\n\r,;]+/', (string) $value);

            foreach ($posiblesValores as $posibleFecha) {
                $fecha = $this->fecha(trim($posibleFecha));

                if ($fecha) {
                    $fechas[] = $fecha;
                }
            }
        }

        return collect($fechas)->unique()->values()->all();
    }

    private function guardarVacacionHistorica(int $empleadoId, ?int $areaId, ?int $liderId, string $fecha): string
    {
        $tipoVacacionesId = $this->obtenerTipoVacacionesId();

        $existeManual = DB::table('permisos_solicitudes')
            ->where('empleado_id', $empleadoId)
            ->whereDate('fecha_inicio', $fecha)
            ->whereDate('fecha_fin', $fecha)
            ->where('motivo', '!=', $this->motivoImportado)
            ->exists();

        if ($existeManual) {
            return 'omitida';
        }

        $data = [
            'tipo_permiso_id' => $tipoVacacionesId,
            'empleado_id' => $empleadoId,
            'area_id' => $areaId,
            'lider_id' => $liderId,
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
            'dias_solicitados' => 1,
            'motivo' => $this->motivoImportado,
            'estatus' => 'historico',
            'formato_recibido' => true,
            'formato_recibido_at' => now(),
            'observaciones_rh' => 'Registro histórico importado desde el archivo de saldos de vacaciones.',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('permisos_solicitudes')->insert($this->filtrarColumnas('permisos_solicitudes', $data));

        return 'creada';
    }

    private function eliminarVacacionesHistoricasImportadas(int $empleadoId): int
    {
        if (! Schema::hasTable('permisos_solicitudes')) {
            return 0;
        }

        return DB::table('permisos_solicitudes')
            ->where('empleado_id', $empleadoId)
            ->where('motivo', $this->motivoImportado)
            ->delete();
    }

    private function obtenerTipoVacacionesId(): ?int
    {
        if (! Schema::hasTable('tipos_permisos')) {
            return null;
        }

        $tipo = DB::table('tipos_permisos')
            ->where(function ($q) {
                if (Schema::hasColumn('tipos_permisos', 'slug')) {
                    $q->where('slug', 'vacaciones');
                }

                $q->orWhere('nombre', 'like', '%Vacaciones%');
            })
            ->first();

        if ($tipo) {
            return $tipo->id;
        }

        $data = [
            'nombre' => 'Vacaciones',
            'slug' => 'vacaciones',
            'descripcion' => 'Días de vacaciones que descuentan saldo disponible.',
            'descuenta_vacaciones' => true,
            'requiere_saldo' => true,
            'requiere_firma_colaborador' => true,
            'requiere_firma_lider' => true,
            'requiere_recepcion_rh' => true,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return DB::table('tipos_permisos')->insertGetId($this->filtrarColumnas('tipos_permisos', $data));
    }

    private function obtenerOCrearArea(string $nombre): array
    {
        $nombre = $this->limpiarTexto($nombre);

        $area = DB::table('areas')->where('nombre', $nombre)->first();

        if ($area) {
            return [$area->id, false];
        }

        $data = [
            'nombre' => $nombre,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return [DB::table('areas')->insertGetId($this->filtrarColumnas('areas', $data)), true];
    }

    private function obtenerOCrearLider(string $nombre, ?int $areaId, ?string $correo = null): array
    {
        $nombre = $this->limpiarTexto($nombre);

        // Primero se busca por nombre normalizado para evitar duplicados por
        // dobles espacios, mayúsculas o acentos. Como respaldo, se usa el correo.
        $nombreNormalizado = $this->normalizarNombre($nombre);
        $lider = DB::table('empleados')->get()->first(function ($item) use ($nombreNormalizado) {
            return $this->normalizarNombre($item->nombre ?? null) === $nombreNormalizado;
        });

        if (! $lider && $correo && Schema::hasColumn('empleados', 'correo')) {
            $lider = DB::table('empleados')
                ->whereRaw('LOWER(correo) = ?', [mb_strtolower($correo)])
                ->where('es_lider', true)
                ->first();
        }

        if ($lider) {
            DB::table('empleados')->where('id', $lider->id)->update($this->filtrarColumnas('empleados', [
                'area_id' => $lider->area_id ?? $areaId,
                'correo' => $correo,
                'es_lider' => true,
                'activo' => true,
                'updated_at' => now(),
            ]));

            return [$lider->id, false];
        }

        $numeroEmpleadoLider = 'LIDER-' . strtoupper(substr(sha1(Str::ascii($nombre)), 0, 12));

        $data = [
            'area_id' => $areaId,
            'numero_empleado' => $numeroEmpleadoLider,
            'nombre' => $nombre,
            'correo' => $correo,
            'es_lider' => true,
            'activo' => true,
            'vacaciones_ajuste' => 0,
            'vacaciones_usados' => 0,
            'vacaciones_pendientes' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return [DB::table('empleados')->insertGetId($this->filtrarColumnas('empleados', $data)), true];
    }

    private function buscarEmpleado(?string $numeroEmpleado, ?string $curp, ?string $rfc, ?string $nombre)
    {
        if ($numeroEmpleado && Schema::hasColumn('empleados', 'numero_empleado')) {
            $empleado = DB::table('empleados')->where('numero_empleado', $numeroEmpleado)->first();
            if ($empleado) return $empleado;
        }

        if ($curp && Schema::hasColumn('empleados', 'curp')) {
            $empleado = DB::table('empleados')->where('curp', $curp)->first();
            if ($empleado) return $empleado;
        }

        if ($rfc && Schema::hasColumn('empleados', 'rfc')) {
            $empleado = DB::table('empleados')->where('rfc', $rfc)->first();
            if ($empleado) return $empleado;
        }

        if ($nombre && Schema::hasColumn('empleados', 'nombre')) {
            return DB::table('empleados')->where('nombre', $nombre)->first();
        }

        return null;
    }

    private function diasVacacionesCorrespondientes(?string $fechaIngreso): int
    {
        if (! $fechaIngreso) {
            return 0;
        }

        $fecha = Carbon::parse($fechaIngreso);
        $anios = (int) $fecha->diffInYears(now());

        if ($anios < 1) {
            return 0;
        }

        return match (true) {
            $anios === 1 => 12,
            $anios === 2 => 14,
            $anios === 3 => 16,
            $anios === 4 => 18,
            $anios === 5 => 20,
            default => 20 + (int) (floor(($anios - 6) / 5) + 1) * 2,
        };
    }

    private function filtrarColumnas(string $table, array $data): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $columns = Schema::getColumnListing($table);

        return collect($data)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->only($columns)
            ->all();
    }

    private function decimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        $value = str_replace(['$', ' '], '', $value);

        if (str_contains($value, ',') && ! str_contains($value, '.')) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }

        $value = preg_replace('/[^0-9.\-]/', '', $value);

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return round((float) $value, 4);
    }

    private function fecha($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $numeric = (float) $value;

            if ($numeric < 20000 || $numeric > 60000) {
                return null;
            }

            try {
                return ExcelDate::excelToDateTimeObject($numeric)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $value);

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/m/y',
            'd-m-y',
            'm/d/Y',
            'm-d-Y',
        ];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);

            if ($date instanceof DateTime) {
                $errors = DateTime::getLastErrors();

                if (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0) {
                    return $date->format('Y-m-d');
                }
            }
        }

        return null;
    }

    private function normalizeHeader($value): string
    {
        $value = trim((string) $value);

        $replacements = [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U',
            'ü' => 'U', 'ñ' => 'N',
        ];

        $value = strtr($value, $replacements);
        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9]+/', '_', $value);

        return trim($value, '_');
    }

    private function limpiarTexto($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', (string) $value));

        return $value !== '' ? $value : null;
    }


    private function limpiarCorreo($value): ?string
    {
        $value = $this->limpiarTexto($value);

        if (! $value) {
            return null;
        }

        // Algunos archivos pegan más de un correo con ; , o saltos de línea.
        // Se conserva el primer correo válido, en minúsculas.
        $candidatos = preg_split('/[;,\s]+/', $value) ?: [];

        foreach ($candidatos as $candidato) {
            $candidato = mb_strtolower(trim($candidato));

            if (filter_var($candidato, FILTER_VALIDATE_EMAIL)) {
                return $candidato;
            }
        }

        return null;
    }

    private function normalizarNombre(?string $nombre): string
    {
        $nombre = Str::ascii($this->limpiarTexto($nombre) ?? '');
        $nombre = mb_strtoupper($nombre);

        return preg_replace('/[^A-Z0-9]/', '', $nombre) ?? '';
    }

    private function normalizarClave(?string $valor): ?string
    {
        $valor = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $valor));

        return $valor !== '' ? $valor : null;
    }
}
