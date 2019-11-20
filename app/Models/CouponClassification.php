<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CouponClassification
 * @package App\Models
 */
class CouponClassification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfKey($query, $key)
    {
        return $query->where('coupon_classifications.key', $key);
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
            return $query->whereIn('coupon_classifications.id', $id);
        } else {
            return !$id ? $query : $query->where('coupon_classifications.id', $id);
        }
    }

    /**
     * @return bool
     */
    public function isReferrer()
    {
        return $this->key == 'referred';
    }

    /**
     * @return bool
     */
    public function isClubNacion()
    {
        return $this->key == 'club';
    }

    /**
     * @return bool
     */
    public function isGeneral()
    {
        return $this->key == 'general';
    }
}
