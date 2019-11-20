<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ReferralInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /** @var User $user */
    public $user;

    /** @var array */
    public $emails;

    /**
     * ReferralInvitation constructor.
     * @param User $user
     * @param array $emails
     */
    public function __construct(User $user, $emails)
    {
        $this->user = $user;
        $this->emails = $emails;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
    	return $this->view('emails.casillerosmailamericas.referrals.mail')
                    ->to($this->emails)
                    // ->bcc('jhesayne@mailamericas.com')
                    ->subject('Invitación a Pickabox');
    }
}
