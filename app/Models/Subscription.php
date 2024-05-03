<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $primaryKey='subscription_id';

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
