<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'price',
        'stock',
        'image',
        'nutritional_info',
        'is_active',
    ];

    protected $casts = [
        'nutritional_info' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
