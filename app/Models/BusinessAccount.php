<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAccount extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessAcountFactory> */
    use HasFactory;

    protected $table = 'business_account';
    
    protected $fillable = [
        'id_business',
        'id_user',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the user 
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Get the business
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'id_business', 'id');
    }
}
