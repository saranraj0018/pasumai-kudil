<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
     protected $casts = [
        'plan_details' => 'array',
        'quantity' => 'array',
        'pack' => 'array',
        'delivery_days' => 'array',
    ];
}
