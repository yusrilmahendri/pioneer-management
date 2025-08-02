<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Str;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;

class ProductController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }  

    public function index(){
        $data = Products::paginate(10);  // 10 is the number of items per page
        return new ProductCollection($data);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:category_products,uuid',
            'status_id' => 'required|exists:statuses,uuid',
            'name_product' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Products::create([
            'uuid' => Str::uuid(),
            'user_id' => auth()->user()->uuid,
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
