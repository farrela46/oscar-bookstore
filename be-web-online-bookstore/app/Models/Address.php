<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasApiTokens, HasFactory;
    protected $fillable = [
        'jalan',
        'kelurahan',
        'kecamatan',
        'kecamatan',
        'kota',
        'kode_pos',
        'email'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
