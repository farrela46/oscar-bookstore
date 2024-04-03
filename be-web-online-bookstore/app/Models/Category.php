<?php

namespace App\Models;

use App\Models\Buku;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

    class Category extends Model
    {
        use HasApiTokens, HasFactory;
        protected $fillable = [
            'name',
        ];

        public function bukus()
        {
            return $this->belongsToMany(Buku::class);
        }
    }
