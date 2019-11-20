<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Timezone
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @mixin \Eloquent
 */
class Timezone extends Model {

    public $timestamps = false;

    protected $fillable = ['name', 'description'];

    protected $hidden = ['id'];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $description
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfTimezoneOffset($query, $description)
    {
        return $description ? $query->where('timezones.description', 'like', "%{$description}%") : $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        return $query->where('timezones.name', $name);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $description
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfDescription($query, $description)
    {
        return $query->where('timezones.description', $description);
    }

}