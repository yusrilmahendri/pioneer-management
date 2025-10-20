<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryBusines;
use App\Models\StatusBusines;

class AddBusines extends Model
{
    /** @use HasFactory<\Database\Factories\AddBusinesFactory> */
    use HasFactory;

    protected $guarded = [];

    public function categoryBusines(){
        return $this->belongsTo(CategoryBusines::class, 'category_busines_id', 'id');
    }

    public function statusBusines(){       
        return $this->belongsTo(StatusBusines::class, 'status_busines_id', 'id');       
    }

}
