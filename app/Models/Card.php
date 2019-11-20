<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Card
 * @package App\Models
 *
 * @property $id
 * @property User $user
 * @property CardBrand $cardBrand
 * @property string $name
 * @property string $token
 * @property integer $bin
 * @property string $status
 * @property boolean $default
 * @property integer $expiry_year
 * @property integer $expiry_month
 * @property Collection cardPaymentGateways
 *
 */
class Card extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'card_brand_id',
        'name',
        'token',
        'bin',
        'status',
        'default',
        'expiry_year',
        'expiry_month',
        'number'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cardBrand()
    {
        return $this->belongsTo(CardBrand::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function cardPaymentGateways()
    {
        return $this->hasMany(CardPaymentGateway::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUserId($query, $id)
    {
        return $query->where('cards.user_id', $id);
    }

    /**
     * @return string|null
     */
    public function getCardBrandName()
    {
        return $this->cardBrand ? $this->cardBrand->brand : null;
    }

    public function getTokenByPaymentGateway(PaymentGateway $paymentGateway)
    {
        $cardPaymentGateway = $this->cardPaymentGateways()->get()->first(function ($c) use ($paymentGateway) {
            return $c->payment_gateway_id == $paymentGateway->id;
        });
        
        return $cardPaymentGateway ? $cardPaymentGateway->token : null;
        
    }
    /**
     * @return PaymentGateway
     */
    public function getFirstPaymentGateway()
    {
        /** @var CardPaymentGateway $cardPaymentGateway */
        $cardPaymentGateway = $this->cardPaymentGateways->first();

        return $cardPaymentGateway ? $cardPaymentGateway->paymentGateway : null;
    }
    
    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }
}
