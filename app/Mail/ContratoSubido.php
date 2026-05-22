<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContratoSubido extends Mailable
{
    use Queueable, SerializesModels;

    public $idAlquiler;
    public $nombreInquilino;
    public $urlPdf;

    public function __construct(int $idAlquiler, ?string $nombreInquilino, string $urlPdf)
    {
        $this->idAlquiler = $idAlquiler;
        $this->nombreInquilino = $nombreInquilino;
        $this->urlPdf = $urlPdf;
    }

    public function build()
    {
        return $this->from('spotstayy@gmail.com', 'SpotStay')
                    ->subject('Nuevo contrato disponible')
                    ->view('emails.contrato_subido')
                    ->with([
                        'idAlquiler' => $this->idAlquiler,
                        'nombreInquilino' => $this->nombreInquilino,
                        'urlPdf' => $this->urlPdf,
                    ]);
    }
}
