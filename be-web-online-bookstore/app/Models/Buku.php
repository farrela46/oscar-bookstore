<?php

namespace App\Models;

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
        'slug',
    ];

    public function getFotoAttribute($value)
    {
        return url('storage/buku_photos/' . basename($value));
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'buku_categories', 'buku_id', 'category_id');
    }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }


}
