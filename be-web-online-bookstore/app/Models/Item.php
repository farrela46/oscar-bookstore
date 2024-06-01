<?php

namespace App\Models;

use App\Models\Buku;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{


    use HasFactory;

    protected $fillable = [
        'order_id',
        'buku_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }
}
