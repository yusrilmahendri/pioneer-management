<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessStatus extends Model
{
    use HasFactory;
    
    protected $table = 'business_status';
    
    protected $fillable = [
        'business_status',  // actual column name
        'created_by',
        'updated_by',
    ];

    /**
     * Get businesses with this status
     */
    public function businesses()
    {
        return $this->hasMany(Business::class, 'id_business_status', 'id');
    }
}
