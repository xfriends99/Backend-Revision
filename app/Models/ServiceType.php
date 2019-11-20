<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceType
 *
 * @property string $key
 * @property string $description
 */
class ServiceType extends Model
{
    protected $fillable = [
        'key',
        'description'
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfKey($query, $key)
    {
        if (is_array($key) && !empty($id)) {
            return $query->whereIn('service_types.key', $key);
        } else {
            return !$key ? $query : $query->where('service_types.key', $key);
        }
    }

    /**
     * @return bool
     */
    public function isCourier()
    {
        return $this->key == 'courier';
    }

    /**
     * @return bool
     */
    public function isPostal()
    {
        return $this->key == 'postal';
    }

}
