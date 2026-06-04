<?php

namespace App\Mail\Permisos;

use App\Models\PermisoSolicitud;
use App\Services\Permisos\PermisoDocumentoService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PermisoDocumentoInicialMail extends Mailable
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

        $mail = $this->subject('Formato de permiso generado - Folio ' . str_pad((string) $this->solicitud->id, 6, '0', STR_PAD_LEFT))
            ->view('emails.permisos.documento_inicial')
            ->with([
                'solicitud' => $this->solicitud,
            ]);

        if ($absolutePath && file_exists($absolutePath)) {
            $mail->attach($absolutePath, [
                'as' => 'formato_permiso_' . $this->solicitud->id . '.docx',
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
        }

        return $mail;
    }
}
