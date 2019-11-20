<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseCheckpoint
 * @package App\Models
 *
 * @property int $purchase_id
 * @property int $checkpoint_code_id
 * @property string $checkpoint_at
 */
class PurchaseCheckpoint extends Model
{
    use SoftDeletes;

    protected $fillable = ['purchase_id', 'checkpoint_code_id', 'checkpoint_at'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function checkpointCode()
    {
        return $this->belongsTo(CheckpointCode::class);
    }

}
