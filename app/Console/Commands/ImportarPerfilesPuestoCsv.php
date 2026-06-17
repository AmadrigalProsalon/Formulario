<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PerfilPuestoCsvController;
use Illuminate\Http\Request;

class ImportarPerfilesPuestoCsv extends Command
{
    protected $signature = 'rh:importar-perfiles {archivo : Ruta del CSV dentro del contenedor o relativa al proyecto}';

    protected $description = 'Importa o actualiza perfiles de puesto desde CSV';

    public function handle(): int
    {
        $archivo = $this->argument('archivo');
        $path = file_exists($archivo) ? $archivo : base_path($archivo);

        if (! file_exists($path)) {
            $this->error("No existe el archivo: {$archivo}");
            return self::FAILURE;
        }

        // Reutilizamos la lógica interna copiando el CSV al storage temporal.
        $stored = 'perfiles-puesto/csv/' . basename($path);
        Storage::disk('local')->put($stored, file_get_contents($path));

        $controller = app(PerfilPuestoCsvController::class);
        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('importarCsv');
        $method->setAccessible(true);
        $resultado = $method->invoke($controller, Storage::disk('local')->path($stored), $stored);

        $this->info("Importación completada. Creados: {$resultado['creados']}. Actualizados: {$resultado['actualizados']}. Omitidos: {$resultado['omitidos']}.");

        return self::SUCCESS;
    }
}
