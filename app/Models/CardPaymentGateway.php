<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CardPaymentGateway
 * @package App\Models
 * @property PaymentGateway $paymentGateway
 */
class CardPaymentGateway extends Model
{
    protected $table = 'card_payment_gateway';

    protected $fillable = [
        'card_id',
        'payment_gateway_id',
        'token',
        'details'
    ];
    
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
    
    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }
}
