<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class Coupons
 * @package App\Models
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property User $user
 * @property int $user_id
 * @property CouponClassification $couponClassification
 * @property integer $max_uses
 * @property float $amount
 * @property float $percent
 * @property float $max_amount
 * @property boolean $active
 * @property Carbon $valid_from
 * @property Carbon $valid_to
 */
class Coupon extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'description',
        'user_id',
        'coupon_classification_id',
        'max_uses',
        'amount',
        'percent',
        'max_amount',
        'active',
        'valid_from',
        'valid_to'
    ];

    protected $dates = ['valid_from', 'valid_to', 'created_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function couponClassification()
    {
        return $this->belongsTo(CouponClassification::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('charged_at');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtBeforeThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('coupons.created_at', '<=', $date . '  23:59:59');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtAfterThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('coupons.created_at', '>=', $date . ' 00:00:00');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('coupons.id', $id);
        } else {
            return !$id ? $query : $query->where('coupons.id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCode($query, $code)
    {
        return $query->where('coupons.code', $code);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUserId($query, $id)
    {
        return $query->where('coupons.user_id', $id);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $used
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUsed($query, $used)
    {
        if ($used) {
            return $query->whereHas('users', function ($q) {
                $q->whereNotNull('charged_at');
            });            
        } else {
            return $query->havingRaw('COUNT(coupon_user.coupon_id) < coupons.max_uses OR coupons.max_uses IS NULL');
        }        
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfClassificationId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('coupons.coupon_classification_id', $id);
        } else {
            return !$id ? $query : $query->where('coupons.coupon_classification_id', $id);
        }
    }

    public function scopeOfStatus($query, $status)
    {
        if (is_array($status) && !empty($status)) {
            return $query->whereIn('coupons.active', $status);
        } else {
            return !$status ? $query : $query->where('coupons.active', $status);
        }
    }

    public function getLastDateOfUse()
    {
        if (!$this->users->isEmpty()) {
            $date = $this->users()->pluck('charged_at')->last();
            return Carbon::parse($date)->format('d/m/Y');
        }
        return '-';
    }

    public function getTotalUses()
    {
        return $this->users()->count();
    }

    /**
     * @return string
     */
    public function getPromoAttribute()
    {
        if ($this->amount && $this->percent) {
            return '$'. intval($this->amount) . ' ó ' . intval($this->percent) .'%';
        } else if ($this->amount) {
            return '$'.intval($this->amount);
        } else {
            return intval($this->percent) .'%';
        }        
    }

    /**
     * @return mixed|string
     */
    public function getCouponClassificationName()
    {
        return $this->couponClassification ? $this->couponClassification->name : '';
    }

    /**
     * @return null|string
     */
    public function getCouponClassificationKey()
    {
        return $this->couponClassification ? $this->couponClassification->key : null;
    }
}
