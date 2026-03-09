<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
    public function products()
    {
        return $this->hasMany(ProductDetail::class, 'category_id', 'id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }

    public function orderDetails()
    {
        return $this->hasManyThrough(
            \App\Models\OrderDetail::class,
            \App\Models\ProductDetail::class,
            'category_id',
            'product_id',
            'id',
            'id'
        );
    }
}
