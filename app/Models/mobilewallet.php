<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mobilewallet extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'mobilewallets';

    protected $fillable =[
        'mobile_number'
    ];
}
