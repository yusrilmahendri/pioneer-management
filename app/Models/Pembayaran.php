<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;
use Illuminate\Support\Str;
use App\Models\Voucher;

class Pembayaran extends Model
{
    /** @use HasFactory<\Database\Factories\PembayaranFactory> */
    use HasFactory;
    
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'user_id',
        'product_id',
        'kode_voucher',
        'count',
        'price',
        'date_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function products() {
        return $this->belongsTo(Products::class, 'product_id', 'uuid');
    }

    public function voucher() {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'uuid');  
    }

    /**
     * Get the user who made this payment
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    /**
     * Calculate total amount (price * count)
     */
    public function getTotalAmountAttribute()
    {
        return $this->price * $this->count;
    }

}
