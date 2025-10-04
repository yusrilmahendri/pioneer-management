<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategoryBusines;

class CategoryBusinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryBusines::insert([
            ['name_busines' => 'Retail'],
            ['name_busines' => 'Food & Beverage'],
            ['name_busines' => 'Health & Wellness'],
            ['name_busines' => 'Technology'],
            ['name_busines' => 'Education'],
            ['name_busines' => 'Finance'],
            ['name_busines' => 'Real Estate'],
            ['name_busines' => 'Entertainment'],
            ['name_busines' => 'Travel & Tourism'],
            ['name_busines' => 'Automotive'],
            ['name_busines' => 'Manufacturing'],
            ['name_busines' => 'Agriculture'],
            ['name_busines' => 'UMKM (FNB)'],
            ['name_busines' => 'Consulting'],
            ['name_busines' => 'Media & Communications'],
            ['name_busines' => 'Logistics & Transportation'],
            ['name_busines' => 'Energy & Utilities'],
            ['name_busines' => 'Fashion & Apparel'],
            ['name_busines' => 'Sports & Recreation'],
            ['name_busines' => 'Perkebunan'],
            ['name_busines' => 'Other'],
        ]);
    }
}
