<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function admin()
    {
        return $this->hasMany(Admin::class);
    }

    public function parent()
    {
        return $this->hasMany(Parents::class);
    }
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }
}
