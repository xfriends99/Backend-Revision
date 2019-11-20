<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CheckpointCode
 * @package App\Models
 *
 * @property string $description_en
 * @property string $description_es
 * @property string $key
 */
class CheckpointCode extends Model
{
    use SoftDeletes;

    protected $fillable = ['description_en', 'description_es', 'key'];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfKey($query, $key)
    {
        return $query->where('checkpoint_codes.key', $key);
    }
}
