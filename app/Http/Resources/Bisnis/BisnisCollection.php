<?php

namespace App\Http\Resources\Bisnis;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Bisnis\BisnisResource;

class BisnisCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'data' => BisnisResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }
}
