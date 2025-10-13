<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AddBusines;

class provinsi extends Model
{
    /** @use HasFactory<\Database\Factories\ProvinsiFactory> */
    use HasFactory;
    protected $guarded = [];

            public function addBusines()
    {
        return $this->hasMany(AddBusines::class, 'category_busines_id');
    }

    public function addBusiness()
    {
        return $this->hasMany(AddBusines::class, 'provinsi_id', 'id');
    }
}
