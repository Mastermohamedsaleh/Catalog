<?php

namespace App\Models;


use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{


protected $fillable = [
    'category_id',
    'name',
    'slug',
    'description',
    'price',
    'stock',
    'main_image',      // الاسم الحقيقي في قاعدة البيانات
    'gallery_images',
    'downloadable_file',
    'video_url',       // الاسم الحقيقي في قاعدة البيانات
    'is_active',
];
public function category()
{
    return $this->belongsTo(Category::class);
}


public function images()
{
    return $this->hasMany(ProductImage::class);
}

public function variants()
{
    return $this->hasMany(ProductVariant::class);
}


}
