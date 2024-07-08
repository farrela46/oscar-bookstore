<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'order_id',
        'bsorder_id',
        'shipping_cost',
        'waybill_id',
        'courier_details'
    ];

    protected $casts = [
        'courier_details' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}