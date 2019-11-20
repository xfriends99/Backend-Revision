<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Township
 * @package App\Models
 */
class Township extends Model
{

    public $timestamps = false;

    protected $fillable = ['town_id', 'name', 'name_alt', 'territorial_code', 'abbreviation_code'];

    protected $hidden = ['id', 'town_id'];

    protected $with = ['town'];

    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    public function zipCodes()
    {
        return $this->hasMany(ZipCode::class);
    }


}