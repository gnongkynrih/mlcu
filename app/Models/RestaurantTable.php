<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    //allow all fields to be mass assigned except id
    protected $guarded = ['id'];
    
    public function tableSessions()
    {
        return $this->hasMany(TableSession::class);
    }
}
