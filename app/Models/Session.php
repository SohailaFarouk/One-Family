<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['patient_list'];

    public function parent()
    {
        return $this->belongsTo(Parents::class);
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
