<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;



class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name_product,
            'description' => $this->deskripsi,
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => $this->whenLoaded('categoryProduct', function () {
                return [
                    'uuid' => $this->categoryProduct->uuid,
                    'name' => $this->categoryProduct->category_product ?? 'Unknown Category',
                ];
            }),
            'status' => $this->whenLoaded('statusProduct', function () {
                return [
                    'uuid' => $this->statusProduct->uuid,
                    'name' => $this->statusProduct->name_status ?? 'Unknown Status',
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'uuid' => $this->user->uuid,
                    'name' => $this->user->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
