<?php

namespace App\Mail;

use App\Models\Platform;
use App\Models\User;
use App\Services\Platform\EmailTemplatingService;
use Illuminate\Mail\Mailable;

class SupportRequested extends Mailable
{
    /** @var string */
    protected $tracking;

    /** @var string */
    protected $support_subject;

    /** @var string */
    protected $body;

    /** @var User */
    protected $user;

    /**
     * ContactReceived constructor.
     * @param string $tracking
     * @param string $subject
     * @param string $body
     * @param User|null $user
     */
    public function __construct($tracking, $subject, $body, User $user = null)
    {
        $this->tracking = $tracking;
        $this->support_subject = $subject;
        $this->body = $body;
        $this->user = $user;
    }

    /**
     * @return Mailable
     */
    public function build()
    {
        /** @var User|null $user */
        $user = $this->user;

        /** @var string $tracking */
        $tracking = $this->tracking;

        /** @var string $subject */
        $subject = $this->support_subject;

        /** @var string $body */
        $body = $this->body;

        /** @var Platform $platform */
        $platform = $user ? $user->platform : current_platform();

        /** @var string $view */
        $view = EmailTemplatingService::getViewByPlatformAndCountry($platform, 'support');

        /** @var Mailable $markdown */
        $markdown = $this->markdown($view, compact('user', 'tracking', 'subject', 'body'))
            ->to(env('MAIL_CONTACT'))
            ->bcc('jhesayne@mailamericas.com')
            ->subject('Nuevo mensaje de soporte');

        if ($user) {
            $markdown->replyTo($user->email);
        }

        return $markdown;
    }
}
