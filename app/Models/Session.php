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
    public function doctor()
    {
        return $this->belongsToMany(Doctor::class , 'doctor_id', 'id');
    }
    public function parent()
    {
        return $this->belongsTo(Parents::class);
    }

    public function getAvailabilityAttribute()
    {
        return $this->sessions()->count();
    }

}
