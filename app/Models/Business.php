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
     * Get the owner of this business (primary user with limited fields, no pivot data)
     */
    public function owner()
    {
        return $this->belongsToMany(User::class, 'business_account', 'id_business', 'id_user')
            ->select(['users.id', 'users.name', 'business_account.id_business', 'business_account.id_user'])
            ->limit(1);
    }

    /**
     * Append formatted owner to JSON
     */
    protected $appends = [];

    /**
     * Custom serialization for owner relationship
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // If owner relationship is loaded, clean it up and make id visible
        if ($this->relationLoaded('owner')) {
            $array['owner'] = $this->owner->map(function ($owner) {
                return [
                    'id' => $owner->getAttributeValue('id'), // Get raw id value, bypassing hidden
                    'name' => $owner->name,
                ];
            })->values()->all();
        }
        
        return $array;
    }

    /**
     * Get products belonging to this business
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'id_business', 'id');
    }
}
