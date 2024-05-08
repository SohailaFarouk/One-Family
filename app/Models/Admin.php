<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function product()
    {
        return $this->belongsToMany(Product::class , 'admin_product','product_id','user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class)->withPivot('sales_report_path');
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
