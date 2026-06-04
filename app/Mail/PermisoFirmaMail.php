<?php

namespace App\Mail;

use App\Models\PermisoFirma;
use App\Models\PermisoSolicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PermisoFirmaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PermisoSolicitud $solicitud,
        public PermisoFirma $firma,
    ) {
    }

    public function build()
    {
        return $this->subject('Firma requerida: ' . $this->solicitud->tipoPermiso?->nombre)
            ->view('emails.permisos.firma')
            ->with([
                'solicitud' => $this->solicitud,
                'firma' => $this->firma,
                'urlFirma' => route('permisos.firma.show', $this->firma->token),
            ]);
    }
}
