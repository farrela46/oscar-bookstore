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

    // Buku.php

    // public function getFotoUrlAttribute()
    // {
    //     return $this->foto ? asset('storage/' . $this->foto) : null;
    // }

    // public function getFotoAttribute($value)
    // {
    //     return url('storage/buku_photos/' . basename($value));
    // }


    public function getFotoUrlAttribute()
    {
        return $this->attributes['foto'] ? asset('storage/buku_photos/' . basename($this->attributes['foto'])) : null;
    }

    public function getFotoAttribute($value)
    {
        return url('storage/buku_photos/' . basename($value));
    }

    public function setFotoAttribute($value)
    {
        $this->attributes['foto'] = 'buku_photos/' . basename($value);
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
