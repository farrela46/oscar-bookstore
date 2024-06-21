<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BukusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bukus')->insert([
            [
                'id' => 1,
                'no_isbn' => '918391823981',
                'judul' => 'Ke Sarang Penyelundup',
                'desc' => 'Julian dan temannya pergi ke sarang penyelundup dan bertemu petualangan baru.',
                'pengarang' => 'Enid Blyton',
                'penerbit' => 'Gramedia Pustaka',
                'tahun_terbit' => '2024-06-02',
                'foto' => 'public/storage/buku_photos/KeSarangPenyelundup.jpg',
                'stok' => 15,
                'harga' => 49000,
                'slug' => 'ke-sarang-penyelundup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'no_isbn' => '924238429238',
                'judul' => 'Terowongan Hantu',
                'desc' => 'Julian dan Dick harus menelusuri terowongan hantu yang misterius.',
                'pengarang' => 'Enid Blyton',
                'penerbit' => 'Gramedia Pustaka',
                'tahun_terbit' => '2024-06-05',
                'foto' => 'public/storage/buku_photos/TerowonganHantu.jpg',
                'stok' => 10,
                'harga' => 49000,
                'slug' => 'terowongan-hantu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
