<?php

namespace App\Services\Permisos;

use App\Models\PermisoSolicitud;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class PermisoDocumentoService
{
    public function generarDocumento(PermisoSolicitud $solicitud): string
    {
        $solicitud->loadMissing(['tipoPermiso', 'empleado.area', 'empleado.lider', 'area', 'lider']);

        $relativePath = 'permisos/documentos/permiso_' . $solicitud->id . '_' . now()->format('Ymd_His') . '.docx';
        $absolutePath = Storage::disk(config('permisos.documentos_disk', 'public'))->path($relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        $templatePath = config('permisos.template_path') ?: config('permisos.template_default');

        if ($templatePath && file_exists($templatePath)) {
            $template = new TemplateProcessor($templatePath);
            foreach ($this->valores($solicitud) as $key => $value) {
                $template->setValue($key, htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8'));
            }
            $template->saveAs($absolutePath);
        } else {
            $this->generarDocumentoBasico($solicitud, $absolutePath);
        }

        $solicitud->update([
            'documento_path' => $relativePath,
            'estatus' => $solicitud->estatus ?: 'formato_generado',
        ]);

        return $relativePath;
    }

    public function absolutePath(string $relativePath): string
    {
        return Storage::disk(config('permisos.documentos_disk', 'public'))->path($relativePath);
    }

    private function valores(PermisoSolicitud $solicitud): array
    {
        $empleado = $solicitud->empleado;
        $lider = $solicitud->lider ?: $empleado?->lider;
        $area = $solicitud->area ?: $empleado?->area;

        return [
            'folio' => $solicitud->id,
            'tipo_permiso' => $solicitud->tipoPermiso?->nombre,
            'nombre_colaborador' => $empleado?->nombre,
            'numero_empleado' => $empleado?->numero_empleado,
            'correo_colaborador' => $empleado?->correo,
            'area' => $area?->nombre,
            'puesto' => $empleado?->puesto,
            'lider' => $lider?->nombre,
            'correo_lider' => $lider?->correo,
            'fecha_inicio' => optional($solicitud->fecha_inicio)->format('d/m/Y'),
            'fecha_fin' => optional($solicitud->fecha_fin)->format('d/m/Y'),
            'dias_solicitados' => $solicitud->dias_solicitados,
            'motivo' => $solicitud->motivo,
            'fecha_solicitud' => optional($solicitud->created_at)->format('d/m/Y H:i'),
            'estatus' => str_replace('_', ' ', $solicitud->estatus ?: 'formato generado'),
        ];
    }

    private function generarDocumentoBasico(PermisoSolicitud $solicitud, string $absolutePath): void
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Formato de solicitud de permiso / ausencia', ['bold' => true, 'size' => 16]);
        $section->addTextBreak();

        foreach ($this->valores($solicitud) as $key => $value) {
            $section->addText(ucfirst(str_replace('_', ' ', $key)) . ': ' . $value);
        }

        $section->addTextBreak(2);
        $section->addText('Firma del líder: ________________________________');
        $section->addTextBreak();
        $section->addText('Firma del colaborador: __________________________');
        $section->addTextBreak();
        $section->addText('Fecha de entrega a RH: __________________________');

        IOFactory::createWriter($phpWord, 'Word2007')->save($absolutePath);
    }
}
