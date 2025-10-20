<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;

class StatusProduct extends Model
{
    /** @use HasFactory<\Database\Factories\StatusProductFactory> */
    use HasFactory;
        protected $guarded = [];

    public function product(){
        return $this->hasMany(Products::class, 'status_id', 'uuid');
    }
}
