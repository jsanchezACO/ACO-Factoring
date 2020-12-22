<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MenssgeReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $msg;
    public $transaccion;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        //
        $this->msg=$message;
        $this->subject=$message['Asunto'];
        $this->transaccion=$message['Transaccion'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.message-enviados');
    }
}
