<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    use HasFactory;
    protected $table = 'parents';
    public $timestamps = false;
    
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'event_id',
       'subscription_id',
       'voucher_id',
       'subscription_date '
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class , 'user_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class , 'parent_product','product_id','user_id' );
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    public function session()
    {
        return $this->hasMany(Session::class , 'user_id');
    }
    public function children()
    {
        return $this->hasMany(Children::class, 'user_id');
    }
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function feedback()
    {
        return $this->belongsToMany(Feedback::class , 'parent_feedback' , 'user_id' , 'feedback_id');
    }
    public function payment()
    {
        return $this->belongsToMany(Payment::class, 'parent_payment', 'payment_id', 'user_id');
    }
}
