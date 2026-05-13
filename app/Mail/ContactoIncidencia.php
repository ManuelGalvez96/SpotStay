<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactoIncidencia extends Mailable
{
    use Queueable, SerializesModels;

    public $incidencia;
    public $asunto;
    public $mensaje;
    public $destinatarioNombre;

    public function __construct($incidencia, $asunto, $mensaje, $destinatarioNombre = null)
    {
        $this->incidencia = $incidencia;
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->destinatarioNombre = $destinatarioNombre;
    }

    public function build()
    {
        return $this->subject($this->asunto)
                    ->view('emails.contacto_incidencia')
                    ->with([
                        'incidencia' => $this->incidencia,
                        'mensaje' => $this->mensaje,
                        'destinatarioNombre' => $this->destinatarioNombre,
                    ]);
    }
}
