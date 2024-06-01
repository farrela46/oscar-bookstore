<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use Exception;

class OrdersController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function createOrder(Request $request)
    {
        $orderId = uniqid();
        $totalPayment = $request->input('amount') + $request->input('selectedCourier.price');

        $orderDetails = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $totalPayment,
            ],
            'customer_details' => [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ],
        ];

        $order = Order::create([
            'user_id' => auth()->id(), 
            'total_payment' => $totalPayment,
            'shipping_cost' => $request->input('selectedCourier.price'),
            'status' => 'pending',
            'courier_details' => $request->input('selectedCourier'),
            'items' => $request->input('items'),
        ]);

        try {
            $snapToken = $this->midtransService->createTransaction($orderDetails);
            $paymentUrl = "https://app.midtrans.com/snap/v2/vtweb/$snapToken";
            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
