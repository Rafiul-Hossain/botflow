<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    use HasFactory;

    // Laravel will automatically use the "categories" table
    // so you don’t need to set $table unless your table has a different name.
    // protected $table = 'categories';


    public function newSubscriptions()
    {
        return $this->hasMany(NewSubscription::class, 'category_id');
    }

    protected $fillable = [
        'category_name',
        'category_type',
        'category_line',
        'category_secret',
        'categroy_icon', 
        'is_refill',
    ];

}
