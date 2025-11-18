<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Expenditure extends Model
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'id_business',
        'id_user',
        'category',
        'description',
        'amount',
        'receipt_image',
        'status',
        'id_user_approved',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($expenditure) {
            if (! $expenditure->getKey()) {
                $expenditure->{$expenditure->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who created this expenditure
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Get the business this expenditure belongs to
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'id_business', 'id');
    }

    /**
     * Get the user who approved this expenditure
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'id_user_approved', 'id');
    }

    /**
     * Scope for pending expenditures
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved expenditures
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected expenditures
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}