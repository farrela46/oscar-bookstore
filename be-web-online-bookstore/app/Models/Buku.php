<?php

namespace App\Models;

use App\Models\Image;
use App\Models\Category;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Buku extends Model
{
    use HasApiTokens, HasFactory;
    protected $fillable = [
        'no_isbn',
        'judul',
        'desc',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'foto',
        'stok',
        'harga',
    ];
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

}
