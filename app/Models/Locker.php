<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Locker
 * @package App\Models
 *
 * @property User $user
 * @property string $code
 */
class Locker extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'user_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCode($query, $code)
    {
        return $query->where('lockers.code', 'ilike', $code);
    }

    /**
     * @return string|null
     */
    public function getUserFullName()
    {
        return $this->user ? $this->user->full_name : null;
    }

    /**
     * @return string|null
     */
    public function getUserEmail()
    {
        return $this->user ? $this->user->email : null;
    }
}
