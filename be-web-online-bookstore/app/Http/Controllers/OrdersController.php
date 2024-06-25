<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BiteshipService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
    protected $midtransService;
    protected $biteshipService;


    public function __construct(MidtransService $midtransService, BiteshipService $biteshipService)
    {
        $this->midtransService = $midtransService;
        $this->biteshipService = $biteshipService;
    }

    public function createOrder(Request $request)
    {
        $transactionId = (string) Str::uuid();

        $totalPayment = $request->input('amount') + $request->input('selectedCourier.price');

        $order = Order::create([
            'user_id' => auth()->id(),
            'total_payment' => $totalPayment,
            'address_id' => $request->input('address_id'),  // Add address_id here
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

            // Create order items
            foreach ($request->input('items') as $item) {
                Item::create([
                    'order_id' => $order->id,
                    'buku_id' => $item['buku_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['totalPrice'],
                ]);
            }

            // Delete cart items after order creation
            Cart::where('user_id', auth()->id())
                ->whereIn('buku_id', array_column($request->input('items'), 'buku_id'))
                ->delete();

            // Return the payment URL
            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserOrders(Request $request)
    {
        $user = $request->user();
        $statusFilter = $request->query('status');

        $orders = Order::where('user_id', $user->id)
            ->with(['items.buku'])
            ->orderBy('created_at', 'desc')
            ->get();

        $now = Carbon::now();
        foreach ($orders as $order) {
            if ($now->diffInDays($order->created_at) > 1 && $order->status !== 'expired') {
                $order->status = 'expired';
                $order->save();
            }
        }

        if ($statusFilter) {
            $orders = $orders->filter(function ($order) use ($statusFilter) {
                return $order->status === $statusFilter;
            });
        }

        return response()->json($orders->values());
    }

    public function getAdminOrders(Request $request)
    {
        $statusFilter = $request->query('status');

        $orders = Order::with(['user', 'items.buku'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($statusFilter) {
            $orders = $orders->filter(function ($order) use ($statusFilter) {
                return $order->status === $statusFilter;
            });
        }



        return response()->json($orders->values());
    }



    public function getOrderStatus(Request $request)
    {
        $orderId = $request->query('order_id');
        if (!$orderId) {
            return response()->json(['error' => 'Order ID is required'], 400);
        }

        $client = new Client();
        $url = "https://api.sandbox.midtrans.com/v2/{$orderId}/status";

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(env('MIDTRANS_SERVER_KEY') . ':'),
                ]
            ]);

            $status = json_decode($response->getBody()->getContents(), true);

            if ($status['status_code'] === "200" && $status['transaction_status'] === "capture") {
                $order = Order::where('transaction_id', $status['order_id'])->first();
                if ($order) {
                    $order->status = 'process';
                    $order->save();
                }
            }

            return response()->json($status);
        } catch (\Exception $e) {
            Log::error('Error retrieving Midtrans order status: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to retrieve order status'], 500);
        }
    }

    public function getOrderDetail($transaction_id)
    {
        $order = Order::with(['items.buku', 'address'])->where('transaction_id', $transaction_id)->first();

        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    //ORDERS

    public function makeOrder(Request $request)
    {
        $validated = $request->validate([
            'origin_contact_name' => 'required|string',
            'origin_contact_phone' => 'required|string',
            'origin_address' => 'required|string',
            'origin_note' => 'nullable|string',
            'origin_postal_code' => 'required|integer',
            'origin_area_id' => 'required|string',
            'destination_contact_name' => 'required|string',
            'destination_contact_phone' => 'required|string',
            'destination_address' => 'required|string',
            'destination_postal_code' => 'required|integer',
            'destination_area_id' => 'required|string',
            'courier_company' => 'required|string',
            'courier_type' => 'required|string',
            'delivery_type' => 'required|string',
            'metadata' => 'nullable|array',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.value' => 'required|numeric',
            'items.*.quantity' => 'required|integer',
            'items.*.height' => 'nullable|numeric',
            'items.*.length' => 'nullable|numeric',
            'items.*.weight' => 'required|numeric',
            'items.*.width' => 'nullable|numeric',
        ]);

        try {
            $response = $this->biteshipService->createOrder($validated);
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}