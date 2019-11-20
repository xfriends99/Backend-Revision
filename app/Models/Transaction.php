<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Transaction
 * @package App\Models
 * @property Invoice $invoice
 * @property Card $card
 * @property TransactionStatus $transactionStatus
 * @property TransactionType $transactionType
 * @property string $external_id
 */
class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'card_id',
        'payment_method_id',
        'transaction_status_id',
        'transaction_type_id',
        'amount',
        'external_id',
        'details'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
    
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    
    public function transactionStatus()
    {
        return $this->belongsTo(TransactionStatus::class);
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function scopeOfExternalId($query, $id)
    {
        return $query->where('transactions.external_id', $id);
    }

    public function getCardFirstPaymentGateway()
    {
        return $this->card ? $this->card->getFirstPaymentGateway() : null;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->transactionStatus ? $this->transactionStatus->isApproved() : false;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->transactionStatus ? $this->transactionStatus->isPending() : false;
    }

    /**
     * @return bool
     */
    public function isDebit()
    {
        return $this->transactionType ? $this->transactionType->isDebit() : false;
    }

    /**
     * @return bool
     */
    public function isRefund()
    {
        return $this->transactionType ? $this->transactionType->isRefund() : false;
    }
}
