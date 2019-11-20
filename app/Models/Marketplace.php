<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Marketplace
 * @package App\Models
 *
 * @property string $name
 * @property string $code
 */
class Marketplace extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'informed_by_user'];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        return $query->where('marketplaces.name', $name);
    }
}
