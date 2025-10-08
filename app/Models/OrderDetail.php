<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'category_id',
        'variant_id',
        'product_name',
        'quantity',
        'net_amount',
        'gross_amount',
        'gst_type',
        'gst_percentage',
        'gst_amount',
        'weight'
    ];


    protected $casts = [
        'weight' => 'float',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
