<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['item_code', 'name', 'price', 'type', 'category', 'image', 'stock'];

    protected $casts = [
        'stock' => 'array',
    ];
}
