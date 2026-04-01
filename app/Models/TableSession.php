<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    protected $guarded = ['id'];

    public function restaurantTable()
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
