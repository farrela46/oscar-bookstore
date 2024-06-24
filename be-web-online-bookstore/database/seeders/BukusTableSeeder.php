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
                'desc' => 'Lima Sekawan adalah karya fiksi untuk anak yang ditulis oleh Enid Blyton. Lima Sekawan menceritakan petualangan seru yang dijalani oleh Julian, Dick, George, Anne, dan Timmy. Ke mana pun mereka pergi, pasti ada petualangan yang seru dan mengasyikkan. Kelima bersahabat ini memiliki karakteristik berbeda-beda yang menambah seru petualangan mereka. Benarkah masih ada penyelundup di Sarang Penyelundup? Lima Sekawan pergi berlibur di sebuah rumah tua dan menemukan tempat-tempat persembunyian rahasia serta lorong-lorong bawah tanah! Mereka juga melihat beberapa orang mengirimkan sinyal-sinyal rahasia ke laut… Siapakah para penyelundup ini sebenarnya?',
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
                'desc' => 'Lima Sekawan adalah karya fiksi untuk anak yang ditulis oleh Enid Blyton. Lima Sekawan menceritakan petualangan seru yang dijalani oleh Julian, Dick, George, Anne, dan Timmy. Ke mana pun mereka pergi, pasti ada petualangan yang seru dan mengasyikkan. Kelima bersahabat ini memiliki karakteristik berbeda-beda yang menambah seru petualangan mereka.  Buku ini bercerita tentang Anne yang sedang berbaring diam-diam di atas bukit kaget. Di susul suara melengking tinggi dan segumpal besar awan putih mengepul dari dalam tanah. Kereta api hantu sam kaki kayu bilang kereta api hantu memang selalu muncul malam-malam dari terowongan tanpa masinis atau penumpang lalu kembali lagi kedalam....',
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
