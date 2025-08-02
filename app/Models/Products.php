<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryProduct;
use App\Models\User;
use App\Models\Status;

class Products extends Model
{
    /** @use HasFactory<\Database\Factories\ProductsFactory> */
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'user_id',
        'category_id',
        'status_id',
        'name_product',
        'deskripsi',
        'price',
        'stock',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (! $product->getKey()) {
                $product->{$product->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function category()
    {
        return $this->belongsTo(CategoryProduct::class, 'category_id', 'uuid');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'uuid');
    }
}
