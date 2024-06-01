<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\MidtransService;

class OrdersController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function createOrder(Request $request)
    {
        $totalPayment = $request->input('amount') + $request->input('selectedCourier.price');
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_payment' => $totalPayment,
            'shipping_cost' => $request->input('selectedCourier.price'),
            'status' => 'pending',
            'courier_details' => json_encode($request->input('selectedCourier')),
            'items' => json_encode($request->input('items')),
        ]);
        $orderId = $order->id;

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
        try {
            $snapToken = $this->midtransService->createTransaction($orderDetails);
            $paymentUrl = $snapToken->redirect_url;
            $order->update(['link'=>$paymentUrl]);

        foreach ($request->input('items') as $item) {
            Item::create([
                'order_id' => $order->id,
                'buku_id' => $item['buku_id'],
                'quantity' => $item['quantity'],
                'price' => $item['totalPrice'],
            ]);
        }

            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getUserOrders(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)
            ->with(['items.buku'])
            ->get();

        return response()->json($orders);
    }
}
