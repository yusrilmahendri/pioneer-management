<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StatusTransaksi;

class StatusTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusTransaksi::insert([
            ['name_status_transaksi' => 'Pending'],
            ['name_status_transaksi' => 'Completed'],
            ['name_status_transaksi' => 'Failed'],
        ]);
    }
}
