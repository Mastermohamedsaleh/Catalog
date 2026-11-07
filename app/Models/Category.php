<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'is_active',
        'parent_id'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

   public function products()
   {
    return $this->hasMany(Product::class);
   }

   public function getRouteKeyName()
{
    return 'slug';
}
}
