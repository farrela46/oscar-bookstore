<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Midtrans\Transaction;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public function handleNotification(Request $request)
    {
        $notif = new Transaction();
        $transaction = $notif->status($request->order_id);
        $order = Order::findOrFail($transaction->order_id);

        switch ($transaction->transaction_status) {
            case 'capture':
            case 'settlement':
                $order->update(['status' => 'paid']);
                break;

            case 'pending':
                $order->update(['status' => 'pending']);
                break;

            case 'deny':
            case 'expire':
            case 'cancel':
                $order->update(['status' => 'failed']);
                break;
        }

        return response()->json(['message' => 'Notification handled']);
    }
}
