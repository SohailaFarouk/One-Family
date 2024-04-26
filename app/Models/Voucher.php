<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable=[
        'voucher_code',
        'voucher_percentage'
    ];

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
    public function parent()
    {
        return $this->hasMany(Parents::class);
    }
}
