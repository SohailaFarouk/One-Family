<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    public $primaryKey = 'order_id';

    protected $fillable = [
        'cart_id',
        'order_amount',
        'order_details',
        'order_number'
    ];

    public $timestamps = false;
    public function cart()
    {
        return $this->hasOne(Cart::class , 'cart_id');
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}

