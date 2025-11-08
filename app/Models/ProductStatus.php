<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStatus extends Model
{
    use HasFactory;
    
    protected $table = 'product_status';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'product_status',  // actual column name
        'created_by',
        'updated_by',
    ];

    /**
     * Get products with this status
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'id_product_status', 'id');
    }
}
