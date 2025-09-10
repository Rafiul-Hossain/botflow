<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_name',
        'api_url',
        'api_key',
        'api_type',
        'api_limit',
        'currency',
        'api_alert',
        'status',
    ];

    protected $casts = [
        'api_limit' => 'float',
        'api_type'  => 'integer',
    ];
}
