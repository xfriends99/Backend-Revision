<?php

namespace App\Notifications;

use App\Models\Country;
use App\Models\Platform;
use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\Platform\EmailTemplatingService;

class ResetPassword extends Notification
{
    use Queueable;

    public $actionUrl;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->actionUrl = action('Auth\ResetPasswordController@showResetForm', $token);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        /** @var Platform $platform */
        $platform = current_platform();

        $view = EmailTemplatingService::getViewByPlatformAndCountry($platform, 'reset_password', null);

        $params = [
            'link' => $this->actionUrl,
            'system' => env('APP_NAME')
        ];

        return  (new MailMessage)
            ->subject('Restablecer la contraseña')
            ->markdown($view, $params);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
