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
            'total' => $this->total(),            // Total number of products
            'current_page' => $this->currentPage(), // Current page number
            'per_page' => $this->perPage(),        // Number of items per page
            'last_page' => $this->lastPage(),      // Last page number
            'from' => $this->firstItem(),          // First item on the current page
            'to' => $this->lastItem(),             // Last item on the current page
        ],
    ];
    }
}
