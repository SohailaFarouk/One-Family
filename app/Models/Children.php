<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Children extends Model
{
    use HasFactory;
    protected $table = 'childrens';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'number_of_children',
        'date_of_birth'
    ];

    public function parent()
    {
        return $this->belongsTo(Parents::class , 'user_id');
    }
    public $timestamps = false;

}
