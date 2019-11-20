<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Client as OauthClient;

/**
 * Class Warehouse
 * @package App\Models
 *
 * @property string $name
 * @property string $address
 * @property string $code
 * @property Country $country
 *
 */
class Warehouse extends Model
{
    protected $fillable = ['name', 'address1', 'address2', 'state', 'city', 'township', 'postal_code', 'code', 'country_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function oauthClients()
    {
        return $this->belongsToMany(OauthClient::class, 'oauth_client_warehouse', 'warehouse_id', 'oauth_client_id')->withTimestamps();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCode($query, $code)
    {
        if (is_array($code) && !empty($code)) {
            return $query->whereIn('warehouses.code', $code);
        } else {
            return !$code ? $query : $query->where('warehouses.code', $code);
        }
    }

    /**
     * @return string
     */
    public function getAddressAttribute()
    {
        return "{$this->address1}, {$this->township}, {$this->city}, {$this->postal_code}, {$this->state}";
    }

    /**
     * @return OauthClient|null
     */
    public function getFirstOauthClient()
    {
        return $this->oauthClients ? $this->oauthClients->first() : null;
    }

    /**
     * @return null|string
     */
    public function getCountryName()
    {
        return $this->country ? $this->country->name : null;
    }

    /**
     * @return null|string
     */
    public function getCountryCode()
    {
        return $this->country ? $this->country->code : null;
    }
}
