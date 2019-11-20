<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class State
 * @package App\Models
 *
 * @property Country $country
 * @property Collection $towns
 * @property string $name
 * @property string $name_alt
 */
class State extends Model
{
    public $timestamps = false;

    protected $fillable = ['country_id', 'name', 'name_alt'];

    protected $hidden = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function towns()
    {
        return $this->hasMany(Town::class);
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
    public function scopeOfCountryId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('states.country_id', $id);
        } else {
            return !$id ? $query : $query->where('states.country_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        if (is_array($name) && !empty($name)) {
            return $query->whereIn('states.name', $name);
        } else {
            return !$name ? $query : $query->where('states.name', $name);
        }
    }
}
