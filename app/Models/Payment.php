<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        "razorpay_payment_id",
        "amount",
        "currency",
        "method",
        "email",
        "phone",
    ];
}
