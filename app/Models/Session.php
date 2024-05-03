<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'session_id';
    protected $fillable = [
        'appointment_id',
        'user_id',
        'cart_id',
        'session_type',
        'session_fees',
        'session_time',
        'session_date'
    ];
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class); 
    }
    public function parent()
    {
        return $this->belongsTo(Parents::class , 'user_id' );
    }

    public function getAvailabilityAttribute()
    {
        return $this->sessions()->count();
    }

}
