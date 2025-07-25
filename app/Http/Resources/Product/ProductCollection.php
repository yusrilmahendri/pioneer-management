<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Product\ProductResource;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'products' => ProductResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
            ],
        ];
    }
}
