<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'address_id',
        'phone',
        'email',
        'status',
        'net_amount',
        'shipping_amount',
        'gross_amount',
        'gst_amount',
        'notes',
        'coupon_id',
        'coupon_amount',
        'shipped_at',
        'cancelled_at',
        'cancellation_reason',
        'refunded_at',
        'delivered_at',
        'created_by',
        'updated_by'
    ];

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class);
    }


    public function payment() {
        return $this->hasOne(Payment::class);
    }


}
