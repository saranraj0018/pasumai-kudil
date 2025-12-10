<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hub extends Model
{
    public function user()
    {
        return $this->hasOne(Admin::class, 'id', 'user_id');
    }

    public function get_city()
    {
        return $this->hasOne(City::class, 'hub_id', 'id');
    }
}
