<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryBusines;
use App\Models\StatusBusines;
use App\Models\provinsi;
use App\Models\Kabupaten;

class AddBusines extends Model
{
    /** @use HasFactory<\Database\Factories\AddBusinesFactory> */
    use HasFactory;

    protected $guarded = [];

    public function categoryBusines(){
        return $this->belongsTo(CategoryBusines::class, 'category_id', 'uuid');
    }

    public function provinsi(){
        return $this->belongsTo(provinsi::class, 'provinsi_id', 'id');
    }

    public function statusBusines(){       
        return $this->belongsTo(StatusBusines::class, 'status_id', 'uuid');       
    }
    public function kabupaten(){       
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id');       
    }
}
