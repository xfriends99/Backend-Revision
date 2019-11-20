<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Platform
 * @package App\Models
 *
 * @property Collection $users
 * @property Collection $warehouses
 * @property Collection $sites
 * @property string $name
 * @property string $domain
 */
class Platform extends Model
{
    use SoftDeletes;

    protected $fillable = ['key', 'name', 'domain'];

    protected $hidden = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class)->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    /**
     * @return null|string
     */
    public function getLayoutKeyAttribute()
    {
        if ($this->isMailamericas()) {
            return 'casillerosmailamericas';
        } else {
            if ($this->isCorreosEcuador()) {
                return 'casillerosecuador';
            }
        }

        return null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeOfKey($query, $key)
    {
        return $query->where('platforms.key', $key);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeOfName($query, $name)
    {
        return $query->where('platforms.name', $name);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $domain
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeOfDomain($query, $domain)
    {
        return $query->where('platforms.domain', $domain);
    }

    /**
     * @return bool
     */
    public function isMailamericas()
    {
        return ($this->key == 'casillerosmailamericas');
    }

    /**
     * @return bool
     */
    public function isCorreosEcuador()
    {
        return ($this->key == 'casillerosecuador');
    }
}
