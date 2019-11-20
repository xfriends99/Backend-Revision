<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IdentificationType
 * @package App\Models
 *
 * @property Country country
 * @property string key
 * @property string description
 */
class IdentificationType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'key',
        'description'
    ];

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
    public function scopeOfCountry($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('identification_types.country_id', $id);
        } else {
            return !$id ? $query : $query->where('identification_types.country_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfKey($query, $key)
    {
        if (is_array($key) && !empty($key)) {
            return $query->whereIn('identification_types.key', $key);
        } else {
            return !$key ? $query : $query->where('identification_types.key', $key);
        }
    }

    public function getCountryName()
    {
        return $this->country ? $this->country->name : null;
    }
}
