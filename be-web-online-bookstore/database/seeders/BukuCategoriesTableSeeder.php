<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BukuCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('buku_categories')->insert([
            ['buku_id' => 1, 'category_id' => 1], 
            ['buku_id' => 1, 'category_id' => 2], 
            ['buku_id' => 2, 'category_id' => 1], 
        ]);
    }
}
