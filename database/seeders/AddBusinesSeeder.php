<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryBusines;
use App\Models\StatusBusines;


class AddBusinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryBusinesIds = CategoryBusines::firstOrFail()->id;
        $statusBusinesIds = StatusBusines::firstOrFail()->id;
        
        DB::table('add_busines')->insert([
            [
                'category_busines_id' => $categoryBusinesIds,
                'status_busines_id'   => $statusBusinesIds,
                'name_busines'        => 'Warung Kopi Santai',
                'provinsi_id'         => '16',
                'provinsi_nama'       => 'Sumatera Selatan',
                'kabupaten_id'        => '1671',
                'kabupaten_nama'      => 'Kota Palembang',
                'start_date'          => '2021-03-12',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'category_busines_id' => $categoryBusinesIds,
                'status_busines_id'   => $statusBusinesIds,
                'name_busines'        => 'Bengkel Motor Jaya',
                'provinsi_id'         => '32',
                'provinsi_nama'       => 'Jawa Barat',
                'kabupaten_id'        => '3273',
                'kabupaten_nama'      => 'Kota Bandung',
                'start_date'          => '2020-06-20',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'category_busines_id' => $categoryBusinesIds,
                'status_busines_id'   => $statusBusinesIds,
                'name_busines'        => 'Toko Buku Pintar',
                'provinsi_id'         => '35',
                'provinsi_nama'       => 'Jawa Timur',
                'kabupaten_id'        => '3578',
                'kabupaten_nama'      => 'Kota Surabaya',
                'start_date'          => '2022-01-15',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'category_busines_id' => $categoryBusinesIds,
                'status_busines_id'   => $statusBusinesIds, 
                'name_busines'        => 'Laundry Express Bersih',
                'provinsi_id'         => '51',
                'provinsi_nama'       => 'Bali',
                'kabupaten_id'        => '5171',
                'kabupaten_nama'      => 'Kota Denpasar',
                'start_date'          => '2023-02-10',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'category_busines_id' => $categoryBusinesIds,
                'status_busines_id'   => $statusBusinesIds,
                'name_busines'        => 'Digital Agency Nusantara',
                'provinsi_id'         => '31',
                'provinsi_nama'       => 'DKI Jakarta',
                'kabupaten_id'        => '3171',
                'kabupaten_nama'      => 'Kota Jakarta Pusat',
                'start_date'          => '2024-04-01',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }
}
