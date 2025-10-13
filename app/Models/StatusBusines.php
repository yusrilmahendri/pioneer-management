<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AddBusines;

class StatusBusines extends Model
{
    /** @use HasFactory<\Database\Factories\StatusBusinesFactory> */
    use HasFactory;
        protected $guarded = [];

        Public function addBusiness(){
            return $this->hasMany(AddBusines::class, 'status_id', 'uuid');
        }
}
