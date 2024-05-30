<?php

namespace App\Http\Controllers;

use Exception;
use Midtrans\Notification;
use Illuminate\Http\Request;
use App\Services\MidtransService;

class PaymentsController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function handleNotification(Request $request)
    {
        try {
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        // TODO set payment status in merchant's database to 'challenge'
                    } else {
                        // TODO set payment status in merchant's database to 'success'
                    }
                }
            } else if ($transaction == 'settlement') {
                // TODO set payment status in merchant's database to 'success'
            } else if ($transaction == 'pending') {
                // TODO set payment status in merchant's database to 'pending'
            } else if ($transaction == 'deny') {
                // TODO set payment status in merchant's database to 'deny'
            } else if ($transaction == 'expire') {
                // TODO set payment status in merchant's database to 'expire'
            } else if ($transaction == 'cancel') {
                // TODO set payment status in merchant's database to 'cancel'
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Notification handled successfully']);
    }
}
