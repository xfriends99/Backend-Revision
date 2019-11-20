<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Address
 * @package App\Models
 *
 * @property User $user
 * @property Country $country
 * @property Collection $purchases
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $township
 * @property string $floor
 * @property string $apartment
 * @property string $number
 * @property string $reference
 *
 */
class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'address1',
        'address2',
        'city',
        'state',
        'postal_code',
        'township',
        'floor',
        'apartment',
        'number',
        'reference',
        'country_id',
        'user_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @return bool
     */
    public function hasPurchases() {
        return $this->purchases->isNotEmpty();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUserId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('addresses.user_id', $id);
        } else {
            return !$id ? $query : $query->where('addresses.user_id', $id);
        }
    }

    /**
     * @return null|string
     */
    public function getCountryCode()
    {
        return $this->country ? $this->country->code : null;
    }

    /**
     * @return null|string
     */
    public function getCountryName()
    {
        return $this->country ? $this->country->name : null;
    }

    /**
     * @return string
     */
    public function getAddressAttribute()
    {
        return "{$this->address1}, {$this->township}, {$this->city}, {$this->postal_code}, {$this->state}";
    }
}
