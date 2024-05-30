<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class MidtransService
{
    public function __construct()
    {
        $config = config('midtrans');

        Config::$serverKey = $config['serverKey'];
        Config::$isProduction = $config['isProduction'];
        Config::$isSanitized = $config['isSanitized'];
        Config::$is3ds = $config['is3ds'];
    }

    public function createTransaction($orderDetails)
    {
        try {
            $transaction = Snap::createTransaction($orderDetails);
            return $transaction->redirect_url;
        } catch (Exception $e) {
            throw new \Exception('Error creating Midtrans transaction: ' . $e->getMessage());
        }
    }
}
