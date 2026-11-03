<?php

namespace App\Models;


use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{


protected $fillable = [
    'name',
    'slug',
    'category_id',
    'price',
    'description',
    'main_image',
    'video_url',
    'is_active',
];
public function category()
{
    return $this->belongsTo(Category::class);
}
}
