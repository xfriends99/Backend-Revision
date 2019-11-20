<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Invoice
 * @package App\Models
 *
 * @property Collection $transactions
 * @property Collection $packages
 * @property float $shipping_cost
 * @property float $additional
 * @property float $subtotal
 * @property float $iva
 * @property float $total_amount
 * @property int $attempts_without_card
 */
class Invoice extends Model
{
    protected $fillable = [
        'number',
        'shipping_cost',
        'additional',
        'subtotal',
        'iva',
        'total_amount',
        'state',
        'charged_at',
        'tariff_code',
        'external_invoice_link',
        'insurance',
        'attempts_without_card'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * @return Transaction|null
     */
    public function getSuccessTransaction()
    {
        return $this->transactions->first(function ($transaction) {
            return $transaction->isApproved();
        });
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->state == 'approved';
    }

    /**
     * @return bool
     */
    public function exceededAttemptsWithoutCard()
    {
        return $this->attempts_without_card >= 5;
    }
}
