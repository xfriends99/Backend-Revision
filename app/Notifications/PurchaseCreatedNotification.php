<?php

namespace App\Notifications;

use App\Models\Address;
use App\Models\Country;
use App\Models\Platform;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Platform\EmailTemplatingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PurchaseCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var User */
    protected $user;

    /** @var Purchase */
    protected $purchase;

    /**
     * PrealertCreatedNotification constructor.
     * @param User $user
     * @param Purchase $purchase
     */
    public function __construct(User $user, Purchase $purchase)
    {
        $this->user = $user;
        $this->purchase = $purchase;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        /** @var Platform $platform */
        $platform = $this->user->platform;

        /** @var Warehouse $warehouse */
        $warehouse = $this->purchase->warehouse;

        /** @var Address $address */
        $address = $this->purchase->address;

        $is_special = $this->user->isPlatformCorreosEcuador() && ($this->purchase->is_mobile_device || $this->purchase->value > 400 || $this->purchase->getWeight() > 4);

        /** @var string $view */
        $view = EmailTemplatingService::getViewByPlatformAndCountry($platform, 'purchase_created', $this->user->country);

        $parameters = [
            'address'                     => $address,
            'link'                        => url('http://www.correosdelecuador.gob.ec/clubcorreos/micuenta'),
            'locker'                      => $this->user->getLockerCode(),
            'tracking'                    => $this->purchase->tracking,
            'user'                        => $this->user,
            'purchase'                    => $this->purchase,
            'purchase_items_descriptions' => $this->purchase->getPurchaseItemsDescriptions(),
            'warehouse'                   => $warehouse,
            'is_special'                  => $is_special,
            'system'                      => env('APP_NAME')
        ];

        return (new MailMessage)
            ->subject(Lang::get('purchase.created.subject'))
            ->bcc('jcieri@mailamericas.com')
            ->bcc('aabraham@mailamericas.com')
            ->bcc('jhesayne@mailamericas.com')
            ->bcc('plabin@mailamericas.com')
            ->markdown($view, $parameters);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
