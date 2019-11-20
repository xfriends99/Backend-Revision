<?php

namespace App\Notifications;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Platform;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\Platform\EmailTemplatingService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DebitPackageNotification extends Notification
{
    use Queueable;

    protected $package;

    /**
     * Create a new notification instance.
     *
     * @param Package $package
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
        /** @var User $user */
        $user = $this->package->user;

        /** @var Platform $platform */
        $platform = $this->package->getUserPlatform();

        /** @var Invoice $invoice */
        $invoice = $this->package->invoice;

        /** @var WorkOrder $workOrder */
        $workOrder = $this->package->workOrder;

        /** @var Card $card */
        $card = $user->getDefaultCard();

        /** @var Transaction $transaction */
        $transaction = $invoice->getSuccessTransaction();
        $transaction_details = json_decode($transaction->details, true);
        $tariffDetails = 'Costo de envío: USD ' . $invoice->total_amount;

        $view = EmailTemplatingService::getViewByPlatformAndCountry($platform, 'debit_package', $user->country);
        $subject = EmailTemplatingService::getSubjectByPlatformAndCountry($platform, 'packages.debited', $user->country);
        $parameters = [
            'authorization_code' => isset($transaction_details['authorization_code']) ? $transaction_details['authorization_code'] : null,
            'link'               => url('/terms'),
            'user'               => $user->first_name,
            'tracking'           => $this->package->tracking,
            'date'               => $workOrder->created_at->format('Y-m-d h:i:s'),
            'description'        => $workOrder->getPurchasesDescription(),
            'quantity'           => $this->package->getWorkOrderPurchasesCount(),
            'amount'             => $invoice->total_amount,
            'number'             => $card->number,
            'tariff_details'     => $tariffDetails,
            'transaction'        => isset($transaction->external_id) ? $transaction->external_id : null,
            'system'             => env('APP_NAME'),
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
