<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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