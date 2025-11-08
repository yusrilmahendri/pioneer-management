<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Business;

class BusinessCategory extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryBusinesFactory> */
    use HasFactory;
    
    protected $table = 'business_category';
    
    protected $fillable = [
        'business_category',  // actual column name
        'created_by',
        'updated_by',
    ];

    /**
     * Get businesses in this category
     */
    public function businesses()
    {
        return $this->hasMany(Business::class, 'id_business_category', 'id');
    }
}
