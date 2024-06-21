<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['id' => 1, 'nama' => 'Novel','created_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['id' => 2, 'nama' => 'Adventure','created_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['id' => 3, 'nama' => 'Horror', 'created_at' => Carbon::now()->format('Y-m-d H:i:s')],
        ]);
    }
}
