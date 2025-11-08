<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
     return [
            'id' => $this->id,
            'name_busines' => $this->name_busines,
            'provinsi' => [
                'id' => $this->provinsi_id,
                'nama' => $this->provinsi_nama,
            ],
            'kabupaten' => [
                'id' => $this->kabupaten_id,
                'nama' => $this->kabupaten_nama,
            ],
            'kategori' => $this->businessCategory->name_busines ?? null,
            'status' =>  $this->businessStatus->name_status_busines ?? null,
            'start_date' => $this->start_date,
            'created_at' => $this->created_at,
        ];
    }
}
