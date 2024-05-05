<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BukuCategory extends Model
{
    use HasApiTokens, HasFactory;
    
    protected $table = 'buku_categories';
    protected $fillable = [
        'buku_id',
        'category_id',
    ];

    public function category() {
        return $this->hasOne(Category::class) ;
    } 
}
