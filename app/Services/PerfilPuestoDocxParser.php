<?php

namespace App\Services;

use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;

class PerfilPuestoDocxParser
{
    public function parse(string $absolutePath, ?string $originalName = null): array
    {
        $phpWord = IOFactory::load($absolutePath);

        $parts = [];
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $txt = $this->extractElementText($element);
                if (trim($txt) !== '') {
                    $parts[] = $txt;
                }
            }
        }

        $rawText = $this->normalizeText(implode("\n", $parts));

        $nombrePuesto = $this->matchFirst($rawText, [
            '/Descriptivo\s+de\s+Puesto\s+(.+?)\s+Código/isu',
            '/Descriptivo\s+de\s+Puesto\s+(.+?)\s+Contenido/isu',
        ]);

        if (! $nombrePuesto && $originalName) {
            $nombrePuesto = Str::of(pathinfo($originalName, PATHINFO_FILENAME))
                ->replace(['_', '-'], ' ')
                ->title()
                ->toString();
        }

        $area = $this->matchFirst($rawText, [
            '/Área\/Departamento\s+(.+?)\s+Puesto\s+al\s+que\s+reporta/isu',
            '/Area\/Departamento\s+(.+?)\s+Puesto\s+al\s+que\s+reporta/isu',
        ]);

        $puestoReporta = $this->matchFirst($rawText, [
            '/Puesto\s+al\s+que\s+reporta\s+(.+?)\s+2\.\s*Descripción/isu',
            '/Puesto\s+al\s+que\s+reporta\s+(.+?)\s+Descripción\s+del\s+Puesto/isu',
        ]);

        $organizacion = $this->matchFirst($rawText, [
            '/Organización\s+(.+?)\s+Área\/Departamento/isu',
            '/Organizacion\s+(.+?)\s+Area\/Departamento/isu',
        ]);

        $fecha = $this->matchFirst($rawText, [
            '/Fecha\s+de\s+Elaboración\s+(.+?)\s+Contenido/isu',
            '/Fecha\s+de\s+Elaboracion\s+(.+?)\s+Contenido/isu',
            '/(\d{1,2}\/[A-Za-zÁÉÍÓÚáéíóúñÑ]{3,}\/?\d{4})/u',
        ]);

        $version = $this->matchFirst($rawText, [
            '/Versión\s+(.+?)\s+Fecha\s+de\s+Elaboración/isu',
            '/Version\s+(.+?)\s+Fecha\s+de\s+Elaboracion/isu',
        ]);

        $descripcion = $this->section($rawText, [
            '2. Descripción del Puesto',
            '2. Descripcion del Puesto',
        ], [
            '3. Objetivo de Puesto',
            '3. Objetivo del Puesto',
        ]);

        $objetivo = $this->section($rawText, [
            '3. Objetivo de Puesto',
            '3. Objetivo del Puesto',
        ], [
            '4. Requerimientos Mínimos',
            '4. Requerimientos Minimos',
        ]);

        $requerimientos = $this->section($rawText, [
            '4. Requerimientos Mínimos',
            '4. Requerimientos Minimos',
        ], [
            '5. Aptitudes',
        ]);

        $cualidades = $this->section($rawText, [
            '5.1 Cualidades',
            '5.1: Cualidades',
            '5.1 CUALIDADES',
        ], [
            '5.2 Habilidades',
            '5.2 HABILIDADES',
        ]);

        $habilidades = $this->section($rawText, [
            '5.2 Habilidades',
            '5.2 HABILIDADES',
        ], [
            '6. Responsabilidades y Actividades',
            '6. Responsabilidades',
        ]);

        $responsabilidades = $this->section($rawText, [
            '6. Responsabilidades y Actividades',
            '6. Responsabilidades',
        ], [
            '7. Modificaciones',
            '8. Lista de Distribución',
        ]);

        return [
            'nombre_puesto' => $this->cleanValue($nombrePuesto ?: 'Perfil sin nombre'),
            'codigo' => $this->cleanValue($this->matchFirst($rawText, ['/Código\s+(.+?)\s+Versión/isu'])),
            'version' => $this->cleanValue($version),
            'fecha_elaboracion' => $this->cleanValue($fecha),
            'organizacion' => $this->cleanValue($organizacion),
            'area_departamento' => $this->cleanValue($area),
            'puesto_reporta' => $this->cleanValue($puestoReporta),
            'descripcion_puesto' => $this->cleanTextBlock($descripcion),
            'objetivo_puesto' => $this->cleanTextBlock($objetivo),
            'requerimientos_minimos' => $this->cleanTextBlock($requerimientos),
            'cualidades' => $this->cleanTextBlock($cualidades),
            'habilidades' => $this->cleanTextBlock($habilidades),
            'responsabilidades_text' => $this->cleanTextBlock($responsabilidades),
            'raw_text' => $rawText,
            'responsabilidades' => $this->splitResponsabilidades($responsabilidades),
            'nivel_ingles_sugerido' => $this->inferirNivelIngles($requerimientos),
            'anios_experiencia_sugeridos' => $this->inferirAniosExperiencia($requerimientos),
        ];
    }

    private function extractElementText($element): string
    {
        if ($element instanceof Table) {
            $rows = [];
            foreach ($element->getRows() as $row) {
                if ($row instanceof Row) {
                    $cells = [];
                    foreach ($row->getCells() as $cell) {
                        if ($cell instanceof Cell) {
                            $cellParts = [];
                            foreach ($cell->getElements() as $cellElement) {
                                $cellParts[] = $this->extractElementText($cellElement);
                            }
                            $cells[] = trim(implode(' ', array_filter($cellParts)));
                        }
                    }
                    $rows[] = implode("\n", array_filter($cells));
                }
            }
            return implode("\n", array_filter($rows));
        }

        if (method_exists($element, 'getElements')) {
            $items = [];
            foreach ($element->getElements() as $child) {
                $items[] = $this->extractElementText($child);
            }
            return implode(' ', array_filter($items));
        }

        if (method_exists($element, 'getText')) {
            $text = $element->getText();
            if (is_string($text) || is_numeric($text)) {
                return (string) $text;
            }
        }

        return '';
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = html_entity_decode(strip_tags($text));
        $text = preg_replace('/[\t ]+/', ' ', $text);
        $text = preg_replace('/\n[ \t]+/', "\n", $text);
        $text = preg_replace('/[ \t]+\n/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    private function matchFirst(string $text, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return trim($m[1] ?? '');
            }
        }
        return null;
    }

    private function section(string $text, array $startNeedles, array $endNeedles): string
    {
        $lower = mb_strtolower($text);
        $startPos = null;
        $startLen = 0;

        foreach ($startNeedles as $needle) {
            $pos = mb_stripos($lower, mb_strtolower($needle));
            if ($pos !== false && ($startPos === null || $pos < $startPos)) {
                $startPos = $pos;
                $startLen = mb_strlen($needle);
            }
        }

        if ($startPos === null) {
            return '';
        }

        $contentStart = $startPos + $startLen;
        $endPos = null;

        foreach ($endNeedles as $needle) {
            $pos = mb_stripos($lower, mb_strtolower($needle), $contentStart);
            if ($pos !== false && ($endPos === null || $pos < $endPos)) {
                $endPos = $pos;
            }
        }

        if ($endPos === null) {
            return trim(mb_substr($text, $contentStart));
        }

        return trim(mb_substr($text, $contentStart, $endPos - $contentStart));
    }

    private function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = preg_replace('/\s+/', ' ', trim($value));
        $value = trim($value, " \t\n\r\0\x0B:.-");
        return $value !== '' ? $value : null;
    }

    private function cleanTextBlock(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        $value = preg_replace('/\n{3,}/', "\n\n", trim($value));
        $value = trim($value, " \t\n\r\0\x0B:");
        return $value !== '' ? $value : null;
    }

    private function splitResponsabilidades(?string $text): array
    {
        if (! $text) {
            return [];
        }

        $knownTitles = [
            'Atención de Consultas',
            'Resolución de Problemas',
            'Registro de Interacciones',
            'Seguimiento',
            'Mejora Continua',
            'Cumplimiento de Procedimientos',
            'Colaboración en Equipo',
        ];

        $items = [];
        foreach ($knownTitles as $index => $title) {
            $pattern = '/' . preg_quote($title, '/') . '\s+(.+?)(?=' . implode('|', array_map(fn ($t) => preg_quote($t, '/'), array_slice($knownTitles, $index + 1))) . '|$)/isu';
            if (preg_match($pattern, $text, $m)) {
                $items[] = [
                    'titulo' => $title,
                    'descripcion' => trim($m[1]),
                    'orden' => $index + 1,
                ];
            }
        }

        if (! empty($items)) {
            return $items;
        }

        return [[
            'titulo' => 'Responsabilidades y actividades',
            'descripcion' => trim($text),
            'orden' => 1,
        ]];
    }

    private function inferirNivelIngles(?string $text): ?string
    {
        $t = mb_strtolower($text ?? '');
        if (str_contains($t, 'avanzado')) return 'Avanzado';
        if (str_contains($t, 'intermedio')) return 'Intermedio';
        if (str_contains($t, 'básico') || str_contains($t, 'basico')) return 'Básico';
        return null;
    }

    private function inferirAniosExperiencia(?string $text): ?string
    {
        $t = mb_strtolower($text ?? '');
        if (preg_match('/mínimo\s*2|minimo\s*2|2\s*años|2\s+años/u', $t)) return '1 a 2 años';
        if (preg_match('/3\s*a\s*5|3\s+años|5\s+años/u', $t)) return '3 a 5 años';
        if (preg_match('/1\s*año|1\s+ano|1\s+años/u', $t)) return '0 a 1 año';
        return null;
    }
}
