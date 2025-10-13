<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AddBusines;

class Kabupaten extends Model
{
    /** @use HasFactory<\Database\Factories\KabupatenFactory> */
    use HasFactory;
        protected $guarded = [];

    public function addBusiness()
    {
        return $this->hasMany(AddBusines::class, 'kabupaten_id', 'id');
    }
}
