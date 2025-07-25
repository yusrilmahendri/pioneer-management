<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Http\Resources\Product\ProductCollection;

class ProductController extends Controller
{
    // public function __construct(){
    //     $this->middleware('auth:sanctum');
    // }  

    public function index(){
        $data = Product::get();
        return new ProductCollection($data);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,uuid',
            'category_id' => 'required|exists:category_products,uuid',
            'status_id' => 'required|exists:status_products,uuid',
            'name_product' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'uuid' => Str::uuid(),
            'user_id' => $validated['user_id'],
            'category_id' => $validated['category_id'],
            'status_id' => $validated['status_id'],
            'name_product' => $validated['name_product'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
        ]);
        return new ProductResource($product);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
