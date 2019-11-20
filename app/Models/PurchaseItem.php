<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseItem
 * @package App\Models
 *
 * @property Purchase $purchase
 * @property int $quantity
 * @property float $amount
 * @property string $description
 * @property string $link
 * @property float $weight
 * @property float $width
 * @property float $length
 * @property float $height
 */
class PurchaseItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'quantity',
        'amount',
        'description',
        'link',
        'weight',
        'width',
        'length',
        'height'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
