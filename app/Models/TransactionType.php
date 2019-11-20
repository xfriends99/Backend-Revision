<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionType
 * @package App\Models
 */
class TransactionType extends Model
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
        return $query->where('transaction_types.key', $key);
    }

    /**
     * @return bool
     */
    public function isDebit()
    {
        return $this->key == 'debit';
    }

    /**
     * @return bool
     */
    public function isRefund()
    {
        return $this->key == 'refund';
    }
}
