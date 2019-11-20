<?php

namespace App\Notifications;

use App\Models\Country;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use App\Services\Platform\EmailTemplatingService;

class VerificationSuccess extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var User */
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        /** @var Platform $platform */
        $platform = $this->user->platform;

        /** @var Country $country */
        $country = $this->user->country;

        /** @var string $view */
        $view = EmailTemplatingService::getViewByPlatformAndCountry($platform, 'verification_success', $country);

        /** @var string $subject */
        $subject = EmailTemplatingService::getSubjectByPlatformAndCountry($platform, 'auth.welcome', $country);

        $params = [
            'user' => $this->user->first_name,
            'locker_code' => $this->user->getLockerCode(),
            'system' => env('APP_NAME')
        ];

        /** @var MailMessage $mailMessage */
        return (new MailMessage)
            ->subject($subject)
            ->bcc('jcieri@mailamericas.com')
            ->markdown($view, $params);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
