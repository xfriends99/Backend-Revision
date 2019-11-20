<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionStatus
 * @package App\Models
 */
class TransactionStatus extends Model
{
    protected $fillable = [
        'key',
        'name'
    ];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfKey($query, $key)
    {
        return $query->where('transaction_statuses.key', $key);
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->key == 'approved';
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->key == 'pending';
    }
}
