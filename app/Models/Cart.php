<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'cart_id';
    protected $fillable = [
        'order_id',
        'event_id',
        'total_amount'
    ];

    public function sessions()
    {
        return $this->hasMany(Session::class, 'cart_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class , 'event_id');
    }
    public function product()
    {
        return $this->belongsToMany(Product::class , 'product_cart' , 'cart_id' , 'product_id');
    }
}
