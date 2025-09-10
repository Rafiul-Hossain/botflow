<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'category_name',
        'category_type',
        'category_line',
        'category_secret',
        'categroy_icon', 
        'is_refill',
    ];
}
