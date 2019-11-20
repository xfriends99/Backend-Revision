<?php

namespace App\Notifications;

use App\Models\Card;
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
use Illuminate\Support\Collection;

class PackageWillBeDebitedNotification extends Notification
{
    use Queueable;

    /** @var  Package */
    protected $package;

    /**
     * Create a new notification instance.
     *
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

        /** @var Invoice $invoice */
        $invoice = $this->package->invoice;

        /** @var Card $card */
        $card = $user->getDefaultCard();

        /** @var Collection $purchases */
        $purchases = $this->package->getWorkOrderPurchases();

        /** @var string $view */
        $view = EmailTemplatingService::getViewByPlatformAndCountry($platform, 'package_will_be_debited', $user->country);

        /** @var string $subject */
        $subject = EmailTemplatingService::getSubjectByPlatformAndCountry($platform, 'packages.will_be_debited', $user->country);

        $purchasesInfo = '';
        /** @var Purchase $purchase */
        foreach ($purchases as $purchase) {
            $purchasesInfo .= "Número de seguimiento: {$purchase->tracking}. Fecha de prealerta: {$purchase->purchased_at}";

            /** @var PurchaseItem $purchaseItem */
            foreach ($purchase->purchaseItems as $purchaseItem) {
                $purchasesInfo .= "\n- Descripción: {$purchaseItem->description}. Cantidad: {$purchaseItem->quantity}";
            }

            $purchasesInfo .= "\n\n";
        }

        $parameters = [
            'user'          => $user->first_name,
            'amount'        => $invoice->total_amount,
            'number'        => $card->number,
            'purchasesInfo' => $purchasesInfo,
            'system'        => env('APP_NAME')
        ];

        return (new MailMessage)
            ->subject($subject)
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
