<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;
use App\Models\AddBusines;

class CategoryBusines extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryBusinesFactory> */
    use HasFactory;
    protected $guarded = [];

    public function products(){
        return $this->hasMany(Products::class, 'category_id', 'uuid');
    }

    public function addBusines()
    {
        return $this->hasMany(AddBusines::class, 'category_busines_id');
    }
}
