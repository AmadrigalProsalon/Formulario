<?php

namespace App\Mail;

use App\Models\Formulario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaRespuesta extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public ?string $filePath;
    public ?Formulario $formulario;

    public function __construct(array $data, ?string $filePath = null, ?Formulario $formulario = null)
    {
        $this->data = $data;
        $this->filePath = $filePath;
        $this->formulario = $formulario;
    }

    public function build()
    {
        $mail = $this->subject('Nueva respuesta RH: ' . ($this->formulario?->titulo ?? 'Formulario'))
            ->view('emails.respuesta')
            ->with([
                'data' => $this->data,
                'formulario' => $this->formulario,
            ]);

        if ($this->filePath && file_exists($this->filePath)) {
            $mail->attach($this->filePath, [
                'as' => 'respuesta_rh.docx',
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
        }

        return $mail;
    }
}
