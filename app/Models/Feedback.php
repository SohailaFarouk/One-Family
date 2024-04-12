<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function parent()
    {
        return $this->hasMany(Parents::class);
    }
}
