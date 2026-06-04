<?php

namespace App\Services\Permisos;

use App\Models\PermisoSolicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;
use Throwable;

class PermisoDocumentoService
{
    public function generarDocumento(PermisoSolicitud $solicitud, bool $incluirFirmas = false): string
    {
        $solicitud->loadMissing(['empleado', 'area', 'lider', 'tipoPermiso']);

        $relativePath = $this->relativePath($solicitud, $incluirFirmas);
        $absolutePath = $this->absolutePublicPath($relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        $templatePath = config('permisos.template_path');

        if ($templatePath && file_exists($templatePath)) {
            $this->generarDesdePlantilla($solicitud, $absolutePath, $templatePath, $incluirFirmas);
        } else {
            $this->generarBasico($solicitud, $absolutePath, $incluirFirmas);
        }

        return $relativePath;
    }

    public function generarDesdePlantilla(PermisoSolicitud $solicitud, string $absolutePath, string $templatePath, bool $incluirFirmas): void
    {
        $template = new TemplateProcessor($templatePath);
        $datos = $this->datosDocumento($solicitud);

        foreach ($datos as $key => $value) {
            $template->setValue($key, $this->limpiarTexto($value));
        }

        $this->insertarFirmaEnPlantilla($template, $solicitud, 'colaborador', $incluirFirmas);
        $this->insertarFirmaEnPlantilla($template, $solicitud, 'lider', $incluirFirmas);

        $template->saveAs($absolutePath);
    }

    public function generarBasico(PermisoSolicitud $solicitud, string $absolutePath, bool $incluirFirmas): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginTop' => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(1.5),
            'marginLeft' => Converter::cmToTwip(1.5),
            'marginRight' => Converter::cmToTwip(1.5),
        ]);

        $section->addText('Formato de solicitud de permiso / ausencia', [
            'bold' => true,
            'size' => 16,
        ]);
        $section->addText('Folio: ' . $this->folio($solicitud), ['bold' => true]);
        $section->addTextBreak(1);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'CCCCCC',
            'cellMargin' => 90,
        ]);

        foreach ($this->datosDocumento($solicitud) as $label => $value) {
            $table->addRow();
            $table->addCell(4200)->addText(str_replace('_', ' ', Str::title($label)), ['bold' => true]);
            $table->addCell(6500)->addText((string) $value);
        }

        $section->addTextBreak(1);
        $section->addText('Firmas', ['bold' => true, 'size' => 13]);

        $firmasTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'CCCCCC',
            'cellMargin' => 90,
        ]);
        $firmasTable->addRow();
        $firmasTable->addCell(5500)->addText('Colaborador', ['bold' => true]);
        $firmasTable->addCell(5500)->addText('Líder / jefe directo', ['bold' => true]);

        $firmasTable->addRow(1800);
        $colaboradorCell = $firmasTable->addCell(5500);
        $liderCell = $firmasTable->addCell(5500);

        if ($incluirFirmas) {
            $this->insertarFirmaEnCelda($colaboradorCell, $solicitud, 'colaborador');
            $this->insertarFirmaEnCelda($liderCell, $solicitud, 'lider');
        } else {
            $colaboradorCell->addText('Pendiente de firma');
            $liderCell->addText('Pendiente de firma');
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($absolutePath);
    }

    public function datosDocumento(PermisoSolicitud $solicitud): array
    {
        $empleado = $this->empleado($solicitud);
        $lider = $this->lider($solicitud);
        $area = $this->area($solicitud);
        $tipo = $this->tipoPermiso($solicitud);

        return [
            'folio' => $this->folio($solicitud),
            'tipo_permiso' => $tipo->nombre ?? $tipo->titulo ?? $solicitud->tipo_permiso ?? 'Permiso',
            'nombre_colaborador' => $empleado->nombre ?? $solicitud->nombre_colaborador ?? 'Sin dato',
            'correo_colaborador' => $empleado->correo ?? $solicitud->correo_colaborador ?? 'Sin dato',
            'area' => $area->nombre ?? $solicitud->area ?? 'Sin dato',
            'puesto' => $empleado->puesto ?? $solicitud->puesto ?? 'Sin dato',
            'lider' => $lider->nombre ?? $solicitud->lider_nombre ?? 'Sin dato',
            'correo_lider' => $lider->correo ?? $solicitud->lider_correo ?? 'Sin dato',
            'fecha_inicio' => optional($solicitud->fecha_inicio)->format('d/m/Y') ?: (string) $solicitud->fecha_inicio,
            'fecha_fin' => optional($solicitud->fecha_fin)->format('d/m/Y') ?: (string) $solicitud->fecha_fin,
            'dias_solicitados' => $solicitud->dias_solicitados ?? '0',
            'motivo' => $solicitud->motivo ?? $solicitud->comentarios ?? 'Sin comentarios',
            'fecha_solicitud' => optional($solicitud->created_at)->format('d/m/Y H:i') ?: now()->format('d/m/Y H:i'),
            'estatus' => $solicitud->estatus ?? 'pendiente',
            'formato_recibido' => $solicitud->formato_recibido ? 'Sí' : 'No',
            'observaciones_rh' => $solicitud->observaciones_rh ?? '',
        ];
    }

    public function documentoAbsoluto(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        if (str_starts_with($relativePath, '/')) {
            return $relativePath;
        }

        return $this->absolutePublicPath($relativePath);
    }

    private function insertarFirmaEnPlantilla(TemplateProcessor $template, PermisoSolicitud $solicitud, string $tipoFirma, bool $incluirFirmas): void
    {
        $placeholder = 'firma_' . $tipoFirma;
        $firmaPath = $incluirFirmas ? $this->firmaAbsoluta($solicitud, $tipoFirma) : null;

        try {
            if ($firmaPath && file_exists($firmaPath)) {
                $template->setImageValue($placeholder, [
                    'path' => $firmaPath,
                    'width' => 160,
                    'height' => 70,
                    'ratio' => true,
                ]);
            } else {
                $template->setValue($placeholder, $incluirFirmas ? 'Firma no encontrada' : 'Pendiente de firma');
            }
        } catch (Throwable $e) {
            // Si la plantilla no tiene ese placeholder, no detenemos el flujo.
        }
    }

    private function insertarFirmaEnCelda($cell, PermisoSolicitud $solicitud, string $tipoFirma): void
    {
        $firmaPath = $this->firmaAbsoluta($solicitud, $tipoFirma);

        if ($firmaPath && file_exists($firmaPath)) {
            $cell->addImage($firmaPath, [
                'width' => 160,
                'height' => 70,
                'ratio' => true,
            ]);
            $firma = $this->firmaRegistro($solicitud, $tipoFirma);
            $cell->addText('Firmado: ' . (isset($firma->firmado_at) ? (string) $firma->firmado_at : ''));
        } else {
            $cell->addText('Firma no encontrada');
        }
    }

    private function firmaAbsoluta(PermisoSolicitud $solicitud, string $tipoFirma): ?string
    {
        $firma = $this->firmaRegistro($solicitud, $tipoFirma);

        if (! $firma || empty($firma->firma_path)) {
            return null;
        }

        $path = $firma->firma_path;

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        return storage_path('app/public/' . $path);
    }

    private function firmaRegistro(PermisoSolicitud $solicitud, string $tipoFirma): ?object
    {
        if (! Schema::hasTable('permiso_firmas')) {
            return null;
        }

        return DB::table('permiso_firmas')
            ->where('permiso_solicitud_id', $solicitud->id)
            ->where('tipo_firma', $tipoFirma)
            ->orderByDesc('id')
            ->first();
    }

    private function relativePath(PermisoSolicitud $solicitud, bool $incluirFirmas): string
    {
        $tipo = $incluirFirmas ? 'firmado' : 'inicial';
        $timestamp = now()->format('Ymd_His');

        return 'permisos/documentos/solicitud_' . $solicitud->id . '/formato_permiso_' . $tipo . '_' . $timestamp . '.docx';
    }

    private function absolutePublicPath(string $relativePath): string
    {
        return storage_path('app/public/' . $relativePath);
    }

    private function folio(PermisoSolicitud $solicitud): string
    {
        return 'PER-' . str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT);
    }

    private function empleado(PermisoSolicitud $solicitud): ?object
    {
        return $solicitud->relationLoaded('empleado') ? $solicitud->empleado : ($solicitud->empleado ?? null);
    }

    private function lider(PermisoSolicitud $solicitud): ?object
    {
        return $solicitud->relationLoaded('lider') ? $solicitud->lider : ($solicitud->lider ?? null);
    }

    private function area(PermisoSolicitud $solicitud): ?object
    {
        return $solicitud->relationLoaded('area') ? $solicitud->area : ($solicitud->area ?? null);
    }

    private function tipoPermiso(PermisoSolicitud $solicitud): ?object
    {
        return $solicitud->relationLoaded('tipoPermiso') ? $solicitud->tipoPermiso : ($solicitud->tipoPermiso ?? null);
    }

    private function limpiarTexto(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
