<?php

namespace App\Notifications;

use App\Models\Country;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Platform;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use App\Services\Platform\EmailTemplatingService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddCardNotification extends Notification
{
    use Queueable;

    /** @var Package $package */
    protected $package;

    /**
     * Create a new notification instance.
     *
     * @param $package
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
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
        $platform = $this->package->getUserPlatform();

        /** @var User $user */
        $user = $this->package->user;

        /** @var Country $country */
        $country = $user->country;

        /** @var Invoice $invoice */
        $invoice = $this->package->invoice;

        $purchasesInfo = '';

        /** @var Purchase $purchase */
        foreach ($this->package->getWorkOrderPurchases() as $purchase) {
            $purchasesInfo .= "Número de seguimiento: {$purchase->tracking}. Fecha de prealerta: {$purchase->purchased_at}";

            /** @var PurchaseItem $purchaseItem */
            foreach ($purchase->purchaseItems as $purchaseItem) {
                $purchasesInfo .= "\n- Descripción: {$purchaseItem->description}. Cantidad: {$purchaseItem->quantity}";
            }

            $purchasesInfo .= "\n\n";
        }

        $view = EmailTemplatingService::getViewByPlatformAndCountry($platform, 'add_card', $country);
        $subject = EmailTemplatingService::getSubjectByPlatformAndCountry($platform, 'packages.add_card', $country);
        $data = [
            'user'         => $user->first_name,
            'purchaseInfo' => $purchasesInfo,
            'total_amount' => $invoice->total_amount,
            'system'       => env('APP_NAME')
        ];

        return (new MailMessage)
            ->subject($subject)
            ->bcc('jcieri@mailamericas.com')
            ->markdown($view, $data);
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
