<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'payment_gateway_id',
        'key',
        'name'
    ];
    
    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfKey($query, $key)
    {
        return $query->where('payment_methods.key', $key);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $payment_gateway_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfPaymentGatewayId($query, $payment_gateway_id)
    {
        return $query->where('payment_methods.payment_gateway_id', $payment_gateway_id);
    }
}
