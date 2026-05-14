<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class NuevaRespuesta extends Mailable
{
    public $data;
    public $filePath;

    public function __construct($data, $filePath)
    {
        $this->data = $data;
        $this->filePath = $filePath;
    }

    public function build()
    {
        return $this->subject('Nueva solicitud de personal')
                    ->view('emails.respuesta')
                    ->attach($this->filePath, [
                        'as' => 'perfil_puesto.docx',
                        'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ]);
    }
}
