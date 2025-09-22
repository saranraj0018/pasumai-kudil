<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function details()
{
   return $this->hasOne(ProductDetail::class, 'product_id', 'id');
}

 public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function variants()
{
    return $this->hasMany(ProductDetail::class, 'product_id','id');
}

}
