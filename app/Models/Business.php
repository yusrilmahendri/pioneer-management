<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BusinessCategory;
use App\Models\BusinessStatus;

class Business extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessFactory> */
    use HasFactory;

    protected $table = 'business';
    protected $guarded = [];

    /**
     * Get the business category
     */
    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class, 'id_business_category', 'id');
    }

    /**
     * Get the business status  
     */
    public function businessStatus()
    {
        return $this->belongsTo(BusinessStatus::class, 'id_business_status', 'id');
    }

    /**
     * Get the users assigned to this business (many-to-many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'business_account', 'id_business', 'id_user');
    }

    /**
     * Get products belonging to this business
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'id_business', 'id');
    }
}
