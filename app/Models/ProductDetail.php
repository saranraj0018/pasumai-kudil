<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{

    protected $fillable = [
        'category_id',
        'sale_price',
        'regular_price',
        'purchase_price',
        'weight',
        'weight_unit',
        'stock',
        'tax_type',
        'tax_percentage',
        'is_featured_product',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'weight_unit', 'id');
    }
}
