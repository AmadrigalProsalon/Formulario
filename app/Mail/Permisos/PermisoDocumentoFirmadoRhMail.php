<?php

namespace App\Mail\Permisos;

use App\Models\PermisoSolicitud;
use App\Services\Permisos\PermisoDocumentoService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PermisoDocumentoFirmadoRhMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PermisoSolicitud $solicitud,
        public string $documentoPath
    ) {
    }

    public function build()
    {
        $absolutePath = app(PermisoDocumentoService::class)->documentoAbsoluto($this->documentoPath);

        $mail = $this->subject('Formato firmado listo para RH - Solicitud #' . $this->solicitud->id)
            ->view('emails.permisos.documento_firmado_rh')
            ->with([
                'solicitud' => $this->solicitud,
            ]);

        if ($absolutePath && file_exists($absolutePath)) {
            $mail->attach($absolutePath, [
                'as' => 'formato_permiso_firmado_' . $this->solicitud->id . '.docx',
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
        }

        return $mail;
    }
}
