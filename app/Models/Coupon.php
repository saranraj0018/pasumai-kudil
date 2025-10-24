<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public function get_order()
    {
        return $this->hasMany(Order::class, 'coupon_id', 'id');
    }
}
