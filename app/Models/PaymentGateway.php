<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PaymentGateway
 * @package App\Models
 * @property string $name
 * @property string $key
 */
class PaymentGateway extends Model
{
    protected $fillable = [
        'key',
        'name'
    ];

    public function cardPaymentGateways()
    {
        return $this->hasMany(CardPaymentGateway::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfKey($query, $key)
    {
        return $query->where('payment_gateways.key', $key);
    }

    /**
     * @return bool
     */
    public function isDlocal()
    {
        return $this->key == 'dlocal';
    }

    /**
     * @return bool
     */
    public function isPaymentez()
    {
        return $this->key == 'paymentez';
    }

    /**
     * @return bool
     */
    public function isBrainTree()
    {
        return $this->key == 'braintree';
    }
}
