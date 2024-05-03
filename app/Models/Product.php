<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $primaryKey = 'product_id';
    protected $fillable=[
        'product_name',
        'product_description',
        'product_specification',
        'product_price',
        'product_type',
        'product_image'
    ];

    public function parents()
    {
        return $this->belongsToMany(Parents::class)->withPivot('quantity');
    }
    public function admin()
    {
        return $this->belongsToMany(Admin::class);
    }
    public function cart()
    {
        return $this->belongsToMany(Cart::class);
    }

}
