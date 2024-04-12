<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    public $timestamps = false;
public function payment()
{
    return $this->hasMany(Payment::class);
}
public function appointment()
{
    return $this->belongsToMany(Appointment::class);
}

}
