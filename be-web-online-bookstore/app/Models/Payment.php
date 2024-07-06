<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'mdtransaction_id',
        'masked_card',
        'payment_type',
        'transaction_time',
        'bank',
        'gross_amount',
        'card_type',
        'payment_option_type',
        'shopeepay_reference_number',
        'reference_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
