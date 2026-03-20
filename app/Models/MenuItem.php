<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'price', 'is_available'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
