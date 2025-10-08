<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute() {
        $statusExpansion = [
            1 => 'Ordered',
            2 => 'On Hold',
            3 => 'Shipped',
            4 => 'Delivered',
            5 => 'Cancelled',
            6 => 'Refunded',
        ];

        return $statusExpansion[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeClassAttribute() {
        $badgeClasses = [
            1 => 'bg-blue-100 text-blue-700',
            2 => 'bg-yellow-100 text-yellow-700',
            3 => 'bg-purple-100 text-purple-700',
            4 => 'bg-green-100 text-green-700',
            5 => 'bg-red-100 text-red-700',
            6 => 'bg-gray-100 text-gray-700',
        ];

        return $badgeClasses[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function userAddress()
{
    return $this->belongsTo(Address::class, 'address_id', 'id');
}

    public function payment() {
        return $this->hasOne(Payment::class, 'order_id', 'id');
    }
}
