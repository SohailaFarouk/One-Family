<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function session()
    {
        return $this->hasMany(Session::class);
    }
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    public function product()
    {
        return $this->belongsToMany(Product::class);
    }
}
