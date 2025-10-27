<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryPartner extends Model
{
  public function get_hub()
    {
        return $this->belongsTo(Hub::class, 'hub_id', 'id');
    }

  public function get_map_address()
    {
        return $this->belongsTo(City::class, 'hub_id', 'hub_id');
    }
}
