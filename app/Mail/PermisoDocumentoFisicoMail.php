<?php

namespace App\Mail;

use App\Models\PermisoSolicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PermisoDocumentoFisicoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PermisoSolicitud $solicitud,
        public string $documentoAbsolutePath,
        public string $destinatarioTipo = 'general'
    ) {}

    public function build()
    {
        $this->solicitud->loadMissing(['tipoPermiso', 'empleado', 'lider', 'area']);

        return $this->subject('Formato de permiso / ausencia #' . $this->solicitud->id)
            ->view('emails.permisos.documento_fisico')
            ->with([
                'solicitud' => $this->solicitud,
                'destinatarioTipo' => $this->destinatarioTipo,
            ])
            ->attach($this->documentoAbsolutePath, [
                'as' => 'formato_permiso_' . $this->solicitud->id . '.docx',
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }
}
