<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fawry extends Model
{
    use HasFactory;
    protected $table = 'fawries';

    public $timestamps = false;
    protected $fillable = [
        'fawry_code'
    ];
}
