<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\CategoryProduct;
use App\Models\BusinessCategory;
use App\Models\StatusProduct;
use App\Models\Pembayaran;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $table = 'product';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'product',          // column name in database
        'description',      // column name in database  
        'price',
        'stock',
        'id_business',
        'id_product_category',
        'id_product_status',
        'created_by',
        'updated_by',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class, 'id_business', 'id');
    }
    
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'id_product_category', 'id');
    }

    public function productStatus()
    {
        return $this->belongsTo(ProductStatus::class, 'id_product_status', 'id');       
    }

    public function transactions()
    {
        return $this->hasMany(Pembayaran::class, 'product_id', 'id');
    }
    
}
