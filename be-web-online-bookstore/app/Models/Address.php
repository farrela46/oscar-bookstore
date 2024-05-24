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
        'province_id',
        'city_id',
        'district_id',
        'detail_address',
        'postal_code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
