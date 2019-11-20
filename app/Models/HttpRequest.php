<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HttpRequest extends Model
{
    protected $fillable = [
        'path',
        'request',
        'response',
        'success',
        'http_code',
        'http_method',
        'errors',
        'manual',
        'headers',
        'created_at'
    ];
}
