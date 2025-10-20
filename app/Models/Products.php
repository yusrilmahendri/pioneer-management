<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CategoryProduct;
use App\Models\CategoryBusines;
use App\Models\StatusProduct;
use App\Models\Pembayaran;

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
    
    public function categoryProduct(){
        return $this->belongsTo(CategoryProduct::class, 'category_id', 'uuid');
    }

    public function categoryBusines(){
        return $this->belongsTo(CategoryBusines::class, 'category_id', 'uuid');
    }

    public function statusProduct(){
        return $this->belongsTo(StatusProduct::class, 'status_id', 'uuid');       
    }

    public function transactions()
    {
        return $this->hasMany(Pembayaran::class, 'product_id', 'uuid');
    }
    
}
