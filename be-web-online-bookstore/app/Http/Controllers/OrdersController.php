<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Buku;
use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BiteshipService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
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

        $items = $request->input('items');

        foreach ($items as $item) {
            $buku = Buku::find($item['buku_id']);
            if (!$buku || $item['quantity'] > $buku->stok) {
                return response()->json(['error' => 'Stok tidak cukup untuk buku: ' . ($buku ? $buku->judul : 'Unknown')], 400);
            }
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'total_payment' => $totalPayment,
            'address_id' => $request->input('address_id'),
            'shipping_cost' => $request->input('selectedCourier.price'),
            'status' => 'pending',
            'courier_details' => json_encode($request->input('selectedCourier')),
            'items' => json_encode($items),
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

            foreach ($items as $item) {
                Item::create([
                    'order_id' => $order->id,
                    'buku_id' => $item['buku_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['totalPrice'],
                ]);
            }

            Cart::where('user_id', auth()->id())
                ->whereIn('buku_id', array_column($items, 'buku_id'))
                ->delete();

            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function createOfflineOrder(Request $request)
    {
        $adminUserId = 1;

        $email = $request->input('email');

        if (empty($email)) {
            $userId = $adminUserId;
        } else {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $userId = $user->id;
        }

        $transactionId = (string) Str::uuid();
        $totalPayment = $request->input('amount');

        $items = $request->input('items');
        $orderItems = [];

        try {
            foreach ($items as $item) {
                $buku = Buku::find($item['buku_id']);

                if (!$buku || $item['quantity'] > $buku->stok) {
                    return response()->json(['error' => 'Stok tidak cukup untuk buku: ' . $buku->judul], 400);
                }
            }

            $order = Order::create([
                'user_id' => $userId,
                'total_payment' => $totalPayment,
                'address_id' => $request->input('address_id', 6),
                'shipping_cost' => 0,
                'status' => 'onsite',
                'courier_details' => json_encode([]),
                'items' => json_encode($items),
                'transaction_id' => $transactionId,
            ]);

            $orderId = $order->id;

            foreach ($items as $item) {
                $buku = Buku::find($item['buku_id']);

                if ($buku) {
                    Item::create([
                        'order_id' => $order->id,
                        'buku_id' => $item['buku_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['totalPrice'],
                    ]);

                    $buku->stok -= $item['quantity'];
                    $buku->sold += $item['quantity'];
                    $buku->save();

                    $orderItems[] = [
                        'buku_id' => $item['buku_id'],
                        'quantity' => $item['quantity'],
                        'totalPrice' => $item['totalPrice'],
                        'judul' => $buku->judul,
                        'foto' => asset('storage/buku_photos/' . basename($buku->foto)),
                    ];
                }
            }

            Cart::where('user_id', $userId)
                ->whereIn('buku_id', array_column($items, 'buku_id'))
                ->delete();

            $response = [
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                ],
                'order' => [
                    'id' => $orderId,
                    'total_payment' => $totalPayment,
                    'items' => $orderItems
                ]
            ];

            return response()->json($response, 200);

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
            if ($order->status === 'pending' && $now->diffInHours($order->created_at) > 24) {
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

    // public function getOrderDetail($transaction_id)
    // {
    //     $order = Order::with(['items.buku', 'address'])->where('transaction_id', $transaction_id)->first();

    //     if ($order) {
    //         return response()->json($order);
    //     } else {
    //         return response()->json(['error' => 'Order not found'], 404);
    //     }
    // }
    // public function getOrderDetail($transaction_id)
    // {
    //     $order = Order::with(['items.buku.reviews', 'address'])
    //         ->where('transaction_id', $transaction_id)
    //         ->first();

    //     if ($order) {
    //         return response()->json($order);
    //     } else {
    //         return response()->json(['error' => 'Order not found'], 404);
    //     }
    // }

    // public function getOrderDetail($transaction_id)
    // {
    //     $order = Order::with([
    //         'items.buku.reviews' => function ($query) {
    //             $query->where('user_id', auth()->id()); 
    //         },
    //         'address'
    //     ])->where('transaction_id', $transaction_id)->first();

    //     if ($order) {
    //         return response()->json($order);
    //     } else {
    //         return response()->json(['error' => 'Order not found'], 404);
    //     }
    // }

    public function getOrderDetail($transaction_id)
    {
        $isAdmin = auth()->user()->role === 'ADMIN';

        $order = Order::with([
            'items.buku.reviews' => function ($query) use ($isAdmin, $transaction_id) {
                $query->whereIn('order_id', function ($subquery) use ($transaction_id) {
                    $subquery->select('orders.id')
                        ->from('orders')
                        ->join('items', 'orders.id', '=', 'items.order_id')
                        ->where('orders.transaction_id', $transaction_id);
                });

                if (!$isAdmin) {
                    $query->where('user_id', auth()->id());
                }
            },
            'address'
        ])->where('transaction_id', $transaction_id)->first();

        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }


    //ORDERS

    // public function makeOrder(Request $request)
    // {
    //     $validated = $request->validate([
    //         'origin_contact_name' => 'required|string',
    //         'origin_contact_phone' => 'required|string',
    //         'origin_address' => 'required|string',
    //         'origin_note' => 'nullable|string',
    //         'origin_postal_code' => 'required|integer',
    //         'origin_area_id' => 'required|string',
    //         'destination_contact_name' => 'required|string',
    //         'destination_contact_phone' => 'required|string',
    //         'destination_address' => 'required|string',
    //         'destination_postal_code' => 'required|integer',
    //         'destination_area_id' => 'required|string',
    //         'courier_company' => 'required|string',
    //         'courier_type' => 'required|string',
    //         'delivery_type' => 'required|string',
    //         'metadata' => 'nullable|array',
    //         'items' => 'required|array',
    //         'items.*.name' => 'required|string',
    //         'items.*.description' => 'nullable|string',
    //         'items.*.value' => 'required|numeric',
    //         'items.*.quantity' => 'required|integer',
    //         'items.*.height' => 'nullable|numeric',
    //         'items.*.length' => 'nullable|numeric',
    //         'items.*.weight' => 'required|numeric',
    //         'items.*.width' => 'nullable|numeric',
    //     ]);

    //     try {
    //         $response = $this->biteshipService->createOrder($validated);
    //         return response()->json($response, 201);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

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
            'order_id' => 'required|integer',
            'item_ids' => 'required|array',
            'item_ids.*.item_id' => 'required|integer',
            'item_ids.*.quantity' => 'required|integer'
        ]);

        DB::beginTransaction();

        try {
            $response = $this->biteshipService->createOrder($validated);

            if (isset($response['id'])) {
                $order = Order::findOrFail($request->order_id);
                $order->bsorder_id = $response['id'];
                $order->waybill_id = $response['courier']['waybill_id'];
                $order->status = 'packing';
                $order->save();

                foreach ($request->item_ids as $item) {
                    $buku = Buku::findOrFail($item['item_id']);
                    $buku->stok -= $item['quantity'];
                    $buku->sold += $item['quantity'];
                    $buku->save();
                }

                DB::commit();
                return response()->json($order, 201);
            } else {
                throw new \Exception('Invalid response from Biteship');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function retrieveAdminOrder($bsorderId)
    {
        try {

            $orderDetails = $this->biteshipService->retrieveOrder($bsorderId);

            return response()->json($orderDetails, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function updateOrderStatus(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|string|in:delivery,delivered,finished'
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);

            $order->status = $validated['status'];
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated to ' . $validated['status'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}