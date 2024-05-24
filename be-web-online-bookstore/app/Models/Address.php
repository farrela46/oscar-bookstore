<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasApiTokens, HasFactory;
    protected $fillable = [
        'user_id',
        'selected_address_id',
        'name',
        'penerima',
        'no_penerima',
        'provinsi',
        'kota',
        'kecamatan',
        'postal_code',
        'label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
