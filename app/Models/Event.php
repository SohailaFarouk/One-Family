<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable=[
        'event_name',
        'event_description',
        'start_date',
        'end_date',
        'event_price',
        'event_location',
        'event_status'
    ];
    protected $primaryKey = 'event_id';

    public function admin()
    {
        return $this->hasMany(Admin::class , 'event_id');
    }

    public function parent()
    {
        return $this->hasMany(Parents::class);
    }
    public function cart()
    {
        return $this->hasMany(Cart::class , 'event_id');
    }
}
