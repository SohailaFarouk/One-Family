<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function admin()
    {
        return $this->belongsToMany(Admin::class)->withPivot('sales_report_path');
    }

}
