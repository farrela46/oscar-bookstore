<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Str;
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
        $transactionId = (string) Str::uuid();

        $totalPayment = $request->input('amount') + $request->input('selectedCourier.price');
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_payment' => $totalPayment,
            'shipping_cost' => $request->input('selectedCourier.price'),
            'status' => 'pending',
            'courier_details' => json_encode($request->input('selectedCourier')),
            'items' => json_encode($request->input('items')),
            'transaction_id' => $transactionId,
        ]);
        $orderId = $order->id;

        $orderDetails = [
            'transaction_details' => [
                'order_id' => $transactionId,
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
            $order->update(['link' => $paymentUrl]);

            foreach ($request->input('items') as $item) {
                Item::create([
                    'order_id' => $order->id,
                    'buku_id' => $item['buku_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['totalPrice'],
                ]);
            }

            $itemIds = array_column($request->input('items'), 'id');
            \Log::info('Deleting cart items with IDs: ', $itemIds);

            Cart::where('user_id', auth()->id())
                ->whereIn('buku_id', array_column($request->input('items'), 'buku_id'))
                ->delete();

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
    
        $now = Carbon::now();
        foreach ($orders as $order) {
            if ($now->diffInDays($order->created_at) > 1) {
                $order->status = 'expired';
            }
        }
    
        return response()->json($orders);
    }
}
