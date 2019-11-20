<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactReceived extends Mailable
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $email;

    /** @var string */
    protected $body;

    /**
     * ContactReceived constructor.
     * @param string $name
     * @param string $email
     * @param string $body
     */
    public function __construct($name, $email, $body)
    {
        $this->name = $name;
        $this->email = $email;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'level'      => 'contact',
            'introLines' => [
                "Se recibio el siguiente mensaje de contacto",
                "Nombre: {$this->name}",
                "Dirección de correo electronico: {$this->email}",
                "Mensaje:",
                $this->body
            ],
            'outroLines' => []
        ];

        return $this->markdown('notifications::email', $data)
            ->to(env('MAIL_CONTACT'))
            ->replyTo($this->email)
            ->bcc('jhesayne@mailamericas.com')
            ->subject('Nuevo mensaje de contacto');
    }
}
