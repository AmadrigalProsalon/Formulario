<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class PerfilPuestoDocxParser
{
    public function parse(string $path): array
    {
        $texto = $this->normalizarTexto($this->extraerTextoDocx($path));

        $nombrePuesto = $this->extraerNombrePuesto($texto, $path);

        $requerimientos = $this->extraerSeccion($texto, [
            '4\\.?\\s*Requerimientos\\s+M[ií]nimos',
            'Requerimientos\\s+M[ií]nimos',
        ], [
            '5\\.?\\s*Aptitudes',
            '5\\.1',
        ]);

        $cualidades = $this->extraerSeccion($texto, [
            '5\\.1\\s*Cualidades',
            'Cualidades',
        ], [
            '5\\.2\\s*Habilidades',
            'Habilidades',
        ]);

        $habilidades = $this->extraerSeccion($texto, [
            '5\\.2\\s*Habilidades',
            'Habilidades',
        ], [
            '6\\.?\\s*Responsabilidades\\s+y\\s+Actividades',
            'Responsabilidades\\s+y\\s+Actividades',
        ]);

        $responsabilidades = $this->extraerSeccion($texto, [
            '6\\.?\\s*Responsabilidades\\s+y\\s+Actividades',
            'Responsabilidades\\s+y\\s+Actividades',
        ], [
            '7\\.?\\s*Modificaciones',
            'Modificaciones',
            '8\\.?\\s*Lista\\s+de\\s+Distribuci[oó]n',
        ]);

        $objetivo = $this->extraerSeccion($texto, [
            '3\\.?\\s*Objetivo\\s+(?:de|del)\\s+Puesto',
            'Objetivo\\s+(?:de|del)\\s+Puesto',
        ], [
            '4\\.?\\s*Requerimientos\\s+M[ií]nimos',
            'Requerimientos\\s+M[ií]nimos',
        ]);

        $descripcion = $this->extraerSeccion($texto, [
            '2\\.?\\s*Descripci[oó]n\\s+del\\s+Puesto',
            'Descripci[oó]n\\s+del\\s+Puesto',
        ], [
            '3\\.?\\s*Objetivo\\s+(?:de|del)\\s+Puesto',
            'Objetivo\\s+(?:de|del)\\s+Puesto',
        ]);

        return [
            'nombre_puesto' => $nombrePuesto,
            'codigo' => $this->extraerValorTablaFlexible($texto, 'Código'),
            'version' => $this->extraerValorTablaFlexible($texto, 'Versión') ?: $this->extraerVersion($texto),
            'fecha_elaboracion' => $this->extraerValorTablaFlexible($texto, 'Fecha de Elaboración') ?: $this->extraerFecha($texto),
            'organizacion' => $this->extraerValorTablaFlexible($texto, 'Organización'),
            'area_departamento' => $this->extraerValorTablaFlexible($texto, 'Área/Departamento'),
            'puesto_reporta' => $this->extraerValorTablaFlexible($texto, 'Puesto al que reporta'),
            'descripcion_puesto' => $descripcion,
            'objetivo_puesto' => $objetivo,
            'requerimientos_minimos' => $requerimientos,
            'cualidades' => $cualidades,
            'habilidades' => $habilidades,
            'responsabilidades' => $responsabilidades,
            'escolaridad_detectada' => $this->extraerLineaPorClave($requerimientos, 'Educación'),
            'experiencia_detectada' => $this->extraerLineaPorClave($requerimientos, 'Experiencia'),
            'ingles_detectado' => $this->normalizarNivelIngles($this->extraerLineaPorClave($requerimientos, 'Inglés')),
            'software_detectado' => $this->extraerSoftware($requerimientos),
            'texto_original' => $texto,
        ];
    }

    private function extraerTextoDocx(string $path): string
    {
        if (! file_exists($path)) {
            throw new RuntimeException("El archivo DOCX no existe: {$path}");
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo DOCX.');
        }

        $texto = '';
        $archivos = ['word/document.xml'];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombre = $zip->getNameIndex($i);

            if (str_starts_with($nombre, 'word/header') || str_starts_with($nombre, 'word/footer')) {
                $archivos[] = $nombre;
            }
        }

        foreach (array_unique($archivos) as $archivo) {
            $xml = $zip->getFromName($archivo);

            if (! $xml) {
                continue;
            }

            $xml = preg_replace('/<w:tab\\s*\\/>/i', ' ', $xml);
            $xml = preg_replace('/<w:br\\s*\\/>/i', "\n", $xml);
            $xml = preg_replace('/<\\/w:tc>/i', "\t", $xml);
            $xml = preg_replace('/<\\/w:tr>/i', "\n", $xml);
            $xml = preg_replace('/<\\/w:p>/i', "\n", $xml);
            $texto .= "\n" . strip_tags($xml);
        }

        $zip->close();

        return html_entity_decode($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = str_replace(["\xc2\xa0", "\t"], ' ', $texto);
        $texto = preg_replace('/[ ]+/u', ' ', $texto);
        $texto = preg_replace('/\R+/u', "\n", $texto);

        return collect(explode("\n", $texto))
            ->map(fn ($linea) => trim($linea))
            ->filter()
            ->values()
            ->implode("\n");
    }

    private function extraerNombrePuesto(string $texto, string $path): string
    {
        if (preg_match('/Descriptivo\\s+de\\s+Puesto\\s*\\n(?P<puesto>.+?)(?:\\n|Código|Contenido)/isu', $texto, $m)) {
            return trim($m['puesto']);
        }

        if (preg_match('/Descriptivo\\s+de\\s+Puesto\\s+(?P<puesto>.+?)(?:\\n|Código|Contenido)/isu', $texto, $m)) {
            return trim($m['puesto']);
        }

        return Str::of(pathinfo($path, PATHINFO_FILENAME))->replace(['_', '-'], ' ')->title()->toString();
    }

    private function extraerSeccion(string $texto, array $inicios, array $finales): ?string
    {
        $inicioRegex = implode('|', $inicios);
        $finalRegex = implode('|', $finales);

        $patrones = [
            '/(?:^|\\n)\\s*(?:' . $inicioRegex . ')\\s*\\n(?P<body>.*?)(?=\\n\\s*(?:' . $finalRegex . ')\\s*(?:\\n|$))/isu',
            '/(?:^|\\n)\\s*(?:' . $inicioRegex . ')\\s*(?P<body>.*?)(?=\\n\\s*(?:' . $finalRegex . ')\\s*(?:\\n|$))/isu',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $texto, $m)) {
                return $this->limpiarBloque($m['body']);
            }
        }

        return null;
    }

    private function limpiarBloque(?string $texto): ?string
    {
        if (! $texto) {
            return null;
        }

        $texto = collect(explode("\n", $texto))
            ->map(fn ($linea) => trim($linea))
            ->filter()
            ->values()
            ->implode("\n");

        return $texto !== '' ? $texto : null;
    }

    private function extraerValorTablaFlexible(string $texto, string $etiqueta): ?string
    {
        $lineas = collect(explode("\n", $texto))->values();
        $etiquetaNormalizada = Str::lower($this->sinAcentos($etiqueta));

        foreach ($lineas as $i => $linea) {
            $lineaNormalizada = Str::lower($this->sinAcentos($linea));

            if ($lineaNormalizada === $etiquetaNormalizada || str_contains($lineaNormalizada, $etiquetaNormalizada)) {
                for ($j = $i + 1; $j <= $i + 5 && $j < $lineas->count(); $j++) {
                    $valor = trim((string) $lineas[$j]);

                    if ($valor !== '' && ! $this->esEtiqueta($valor)) {
                        return $valor;
                    }
                }
            }
        }

        return null;
    }

    private function esEtiqueta(string $valor): bool
    {
        $valor = Str::lower($this->sinAcentos(trim($valor)));
        $etiquetas = [
            'codigo',
            'version',
            'fecha de elaboracion',
            'organizacion',
            'area/departamento',
            'puesto al que reporta',
            'puesto',
            'firma',
        ];

        return in_array($valor, $etiquetas, true);
    }

    private function extraerVersion(string $texto): ?string
    {
        if (preg_match('/\\n(?P<version>\\d{2})\\n\\d{1,2}\\/[A-Za-zÁÉÍÓÚáéíóú]+\\/\\d{4}/u', $texto, $m)) {
            return trim($m['version']);
        }

        return null;
    }

    private function extraerFecha(string $texto): ?string
    {
        if (preg_match('/(?P<fecha>\\d{1,2}\\/[A-Za-zÁÉÍÓÚáéíóú]+\\/\\d{4})/u', $texto, $m)) {
            return trim($m['fecha']);
        }

        return null;
    }

    private function extraerLineaPorClave(?string $texto, string $clave): ?string
    {
        if (! $texto) {
            return null;
        }

        if (preg_match('/' . preg_quote($clave, '/') . '\\s*:\\s*(?P<valor>.+?)(?:\\n|$)/iu', $texto, $m)) {
            return trim($m['valor']);
        }

        foreach (explode("\n", $texto) as $linea) {
            if (str_contains(Str::lower($this->sinAcentos($linea)), Str::lower($this->sinAcentos($clave)))) {
                return trim($linea);
            }
        }

        return null;
    }

    private function extraerSoftware(?string $texto): ?string
    {
        if (! $texto) {
            return null;
        }

        $lineas = collect(explode("\n", $texto))
            ->filter(function ($linea) {
                $linea = Str::lower($this->sinAcentos($linea));

                return str_contains($linea, 'crm')
                    || str_contains($linea, 'sistema')
                    || str_contains($linea, 'herramienta')
                    || str_contains($linea, 'software');
            })
            ->values()
            ->toArray();

        return count($lineas) ? implode("\n", $lineas) : null;
    }

    private function normalizarNivelIngles(?string $valor): ?string
    {
        if (! $valor) {
            return null;
        }

        $v = Str::lower($this->sinAcentos($valor));

        if (str_contains($v, 'avanzado')) {
            return 'Avanzado';
        }

        if (str_contains($v, 'intermedio')) {
            return 'Intermedio';
        }

        if (str_contains($v, 'basico')) {
            return 'Básico';
        }

        return null;
    }

    private function sinAcentos(string $texto): string
    {
        return strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        ]);
    }
}
