<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function session()
    {
        return $this->hasOne(Session::class);
    }
    public function doctor()
    {
        return $this->belongsToMany(Doctor::class);
    }
}
