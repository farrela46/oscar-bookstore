<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Buku;
use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use GuzzleHttp\Client;
use App\Models\Payment;
use App\Models\Shipment;
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
        DB::beginTransaction();

        try {

            $transactionId = (string) Str::uuid();


            $totalPayment = $request->input('amount');
            $shippingCost = $request->input('selectedCourier.price');
            $items = $request->input('items');


            foreach ($items as $item) {
                $buku = Buku::find($item['buku_id']);
                if (!$buku || $item['quantity'] > $buku->stok) {
                    throw new Exception('Stok tidak cukup untuk buku: ' . ($buku ? $buku->judul : 'Unknown'));
                }
            }


            $order = Order::create([
                'user_id' => auth()->id(),
                'total_payment' => $totalPayment,
                'address_id' => $request->input('address_id'),
                'status' => 'pending',
            ]);

            Shipment::create([
                'order_id' => $order->id,
                'shipping_cost' => $shippingCost,
                'courier_details' => json_encode($request->input('selectedCourier')),
                'bsorder_id' => $request->input('bsorder_id'),
                'waybill_id' => $request->input('waybill_id')
            ]);

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


            $orderDetails = [
                'transaction_details' => [
                    'order_id' => $transactionId,
                    'gross_amount' => $totalPayment + $shippingCost,
                ],
                'customer_details' => [
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                ],
            ];

            $snapToken = $this->midtransService->createTransaction($orderDetails);
            $paymentUrl = $snapToken->redirect_url;

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $transactionId,
                'link' => $paymentUrl,
                'mdtransaction_id' => $snapToken->transaction_id ?? $transactionId, // Gunakan UUID jika tidak tersedia
                'gross_amount' => $totalPayment + $shippingCost,
                'transaction_time' => now(),
                'payment_type' => $snapToken->payment_type ?? null,
            ]);

            DB::commit();

            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
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

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                $buku = Buku::find($item['buku_id']);

                if (!$buku || $item['quantity'] > $buku->stok) {
                    return response()->json(['error' => 'Stok tidak cukup untuk buku: ' . ($buku ? $buku->judul : 'Unknown')], 400);
                }
            }

            $order = Order::create([
                'user_id' => $userId,
                'total_payment' => $totalPayment,
                'address_id' => $request->input('address_id', 6),
                'status' => 'onsite',
            ]);

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

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $transactionId,
                'amount' => $totalPayment,
            ]);

            Shipment::create([
                'order_id' => $order->id,
                'bsorder_id' => null,
                'waybill_id' => null,
                'shipping_cost' => 0,
                'courier_details' => json_encode([]),
            ]);

            Cart::where('user_id', auth()->id())
                ->whereIn('buku_id', array_column($items, 'buku_id'))
                ->delete();

            DB::commit();

            $response = [
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                ],
                'order' => [
                    'id' => $order->id,
                    'total_payment' => $totalPayment,
                    'items' => $orderItems
                ]
            ];

            return response()->json($response, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }











    public function getUserOrders(Request $request)
    {
        $userId = $request->user()->id;
        $statusFilter = $request->query('status');

        $query = "
    SELECT orders.*, items.*, bukus.*, payments.*, shipments.*, 
           orders.id as order_id, items.id as item_id, bukus.id as buku_id, payments.id as payment_id, shipments.id as shipment_id
    FROM orders
    LEFT JOIN items ON items.order_id = orders.id
    LEFT JOIN bukus ON bukus.id = items.buku_id
    LEFT JOIN payments ON payments.order_id = orders.id
    LEFT JOIN shipments ON shipments.order_id = orders.id
    WHERE orders.user_id = ?
    ORDER BY orders.created_at DESC
    ";

        if ($statusFilter) {
            $query = "
        SELECT orders.*, items.*, bukus.*, payments.*, shipments.*, 
               orders.id as order_id, items.id as item_id, bukus.id as buku_id, payments.id as payment_id, shipments.id as shipment_id
        FROM orders
        LEFT JOIN items ON items.order_id = orders.id
        LEFT JOIN bukus ON bukus.id = items.buku_id
        LEFT JOIN payments ON payments.order_id = orders.id
        LEFT JOIN shipments ON shipments.order_id = orders.id
        WHERE orders.user_id = ? AND orders.status = ?
        ORDER BY orders.created_at DESC
        ";
            $orders = DB::select($query, [$userId, $statusFilter]);
        } else {
            $orders = DB::select($query, [$userId]);
        }

        $formattedOrders = collect($orders)->groupBy('order_id')->map(function ($orderGroup) {
            $order = $orderGroup->first();
            $items = $orderGroup->map(function ($item) {
                return [
                    'id' => $item->item_id,
                    'order_id' => $item->order_id,
                    'buku_id' => $item->buku_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'buku' => [
                        'id' => $item->buku_id,
                        'no_isbn' => $item->no_isbn,
                        'judul' => $item->judul,
                        'desc' => $item->desc,
                        'pengarang' => $item->pengarang,
                        'penerbit' => $item->penerbit,
                        'tahun_terbit' => $item->tahun_terbit,
                        'foto' => asset('storage/buku_photos/' . basename($item->foto)),
                        'stok' => $item->stok,
                        'sold' => $item->sold,
                        'harga' => $item->price / $item->quantity,
                        'slug' => $item->slug,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at
                    ]
                ];
            });

            return [
                'id' => $order->order_id,
                'user_id' => $order->user_id,
                'address_id' => $order->address_id,
                'total_payment' => $order->total_payment,
                'status' => $order->status,
                'items' => $items,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'payment' => [
                    'id' => $order->payment_id,
                    'transaction_id' => $order->transaction_id,
                    'mdtransaction_id' => $order->mdtransaction_id,
                    'masked_card' => $order->masked_card,
                    'payment_type' => $order->payment_type,
                    'transaction_time' => $order->transaction_time,
                    'bank' => $order->bank,
                    'gross_amount' => $order->gross_amount,
                    'card_type' => $order->card_type,
                    'payment_option_type' => $order->payment_option_type,
                    'shopeepay_reference_number' => $order->shopeepay_reference_number,
                    'reference_id' => $order->reference_id,
                    'link' => $order->link,
                ],
                'shipment' => [
                    'id' => $order->shipment_id,
                    'bsorder_id' => $order->bsorder_id,
                    'shipping_cost' => $order->shipping_cost,
                    'waybill_id' => $order->waybill_id,
                    'courier_details' => json_decode($order->courier_details, true)
                ],
            ];
        })->values();

        foreach ($formattedOrders as $order) {
            if ($order['status'] === 'pending' && Carbon::parse($order['created_at'])->diffInHours(Carbon::now()) > 24) {
                DB::table('orders')
                    ->where('id', $order['id'])
                    ->update(['status' => 'expired']);
                $order['status'] = 'expired';
            }
        }

        return response()->json($formattedOrders);
    }





    public function getAdminOrders(Request $request)
    {
        $statusFilter = $request->query('status');

        $query = "
        SELECT orders.*, 
               users.name, users.email, users.role, users.no_telp, users.email_verified_at,
               payments.transaction_id, payments.mdtransaction_id, payments.masked_card, payments.payment_type, 
               payments.transaction_time, payments.bank, payments.gross_amount, payments.card_type, 
               payments.payment_option_type, payments.shopeepay_reference_number, payments.reference_id,
               payments.link, -- Tambahkan ini untuk mengambil link dari payments
               shipments.bsorder_id, shipments.shipping_cost, shipments.waybill_id, shipments.courier_details
        FROM orders
        LEFT JOIN users ON users.id = orders.user_id
        LEFT JOIN payments ON payments.order_id = orders.id
        LEFT JOIN shipments ON shipments.order_id = orders.id
        ORDER BY orders.created_at DESC
    ";

        if ($statusFilter) {
            $query = "
            SELECT orders.*, 
                   users.name, users.email, users.role, users.no_telp, users.email_verified_at,
                   payments.transaction_id, payments.mdtransaction_id, payments.masked_card, payments.payment_type, 
                   payments.transaction_time, payments.bank, payments.gross_amount, payments.card_type, 
                   payments.payment_option_type, payments.shopeepay_reference_number, payments.reference_id,
                   payments.link, -- Tambahkan ini untuk mengambil link dari payments
                   shipments.bsorder_id, shipments.shipping_cost, shipments.waybill_id, shipments.courier_details
            FROM orders
            LEFT JOIN users ON users.id = orders.user_id
            LEFT JOIN payments ON payments.order_id = orders.id
            LEFT JOIN shipments ON shipments.order_id = orders.id
            WHERE orders.status = ?
            ORDER BY orders.created_at DESC
        ";
            $orders = DB::select($query, [$statusFilter]);
        } else {
            $orders = DB::select($query);
        }

        $formattedOrders = collect($orders)->groupBy('id')->map(function ($orderGroup) {
            $order = $orderGroup->first();

            $items = DB::table('items')
                ->leftJoin('bukus', 'bukus.id', '=', 'items.buku_id')
                ->where('items.order_id', $order->id)
                ->select(
                    'items.id as item_id',
                    'items.order_id',
                    'items.buku_id',
                    'items.quantity',
                    'items.price',
                    'items.created_at',
                    'items.updated_at',
                    'bukus.id as buku_id',
                    'bukus.no_isbn',
                    'bukus.judul',
                    'bukus.desc',
                    'bukus.pengarang',
                    'bukus.penerbit',
                    'bukus.tahun_terbit',
                    'bukus.foto',
                    'bukus.stok',
                    'bukus.sold',
                    'bukus.harga',
                    'bukus.slug'
                )
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->item_id,
                        'order_id' => $item->order_id,
                        'buku_id' => $item->buku_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                        'buku' => [
                            'id' => $item->buku_id,
                            'no_isbn' => $item->no_isbn,
                            'judul' => $item->judul,
                            'desc' => $item->desc,
                            'pengarang' => $item->pengarang,
                            'penerbit' => $item->penerbit,
                            'tahun_terbit' => $item->tahun_terbit,
                            'foto' => asset('storage/buku_photos/' . basename($item->foto)),
                            'stok' => $item->stok,
                            'sold' => $item->sold,
                            'harga' => $item->price / $item->quantity,
                            'slug' => $item->slug,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at
                        ]
                    ];
                });

            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'address_id' => $order->address_id,
                'transaction_id' => $order->transaction_id,
                'bsorder_id' => $order->bsorder_id,
                'total_payment' => $order->total_payment,
                'shipping_cost' => $order->shipping_cost,
                'waybill_id' => $order->waybill_id,
                'status' => $order->status,
                'courier_details' => $order->courier_details,
                'items' => $items,
                'link' => $order->link,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'user' => [
                    'id' => $order->user_id,
                    'name' => $order->name,
                    'email' => $order->email,
                    'role' => $order->role,
                    'no_telp' => $order->no_telp,
                    'email_verified_at' => $order->email_verified_at,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at
                ]
            ];
        })->values();

        return response()->json($formattedOrders);
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

            if ($status['status_code'] === "200" && in_array($status['transaction_status'], ["capture", "settlement"])) {
                $payment = Payment::where('transaction_id', $status['order_id'])->first();
                if ($payment) {
                    // Update data di tabel payments
                    $payment->update([
                        'masked_card' => $status['masked_card'] ?? null,
                        'payment_type' => $status['payment_type'] ?? null,
                        'transaction_time' => $status['transaction_time'] ?? null,
                        'bank' => $status['bank'] ?? null,
                        'gross_amount' => $status['gross_amount'] ?? null,
                        'card_type' => $status['card_type'] ?? null,
                        'mdtransaction_id' => $status['transaction_id'] ?? null,
                    ]);

                    $order = Order::find($payment->order_id);
                    if ($order) {
                        $order->status = 'process';
                        $order->save();
                    }
                }
            } else {
                // Handle status yang tidak sukses atau error
                // Contoh: batalkan atau tandai order sebagai gagal
                // Anda bisa menambahkan logika khusus untuk status lain seperti "deny", "cancel", atau "expire"
            }

            return response()->json($status);
        } catch (\Exception $e) {
            Log::error('Error retrieving Midtrans order status: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to retrieve order status'], 500);
        }
    }



    // public function getOrderDetail($transaction_id)
    // {
    //     $isAdmin = auth()->user()->role === 'ADMIN';

    //     $order = Order::with([
    //         'items.buku.reviews' => function ($query) use ($isAdmin, $transaction_id) {
    //             $query->whereIn('order_id', function ($subquery) use ($transaction_id) {
    //                 $subquery->select('orders.id')
    //                     ->from('orders')
    //                     ->join('items', 'orders.id', '=', 'items.order_id')
    //                     ->where('orders.transaction_id', $transaction_id);
    //             });

    //             if (!$isAdmin) {
    //                 $query->where('user_id', auth()->id());
    //             }
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
        $userId = auth()->id();

        $query = "
    SELECT 
        orders.id AS order_id,
        orders.user_id,
        orders.address_id,
        orders.status,
        orders.total_payment,
        orders.created_at AS order_created_at,
        orders.updated_at AS order_updated_at,
        items.id AS item_id,
        items.order_id,
        items.buku_id,
        items.quantity,
        items.price AS item_price,
        items.created_at AS item_created_at,
        items.updated_at AS item_updated_at,
        bukus.id AS buku_id,
        bukus.no_isbn,
        bukus.judul,
        bukus.desc,
        bukus.pengarang,
        bukus.penerbit,
        bukus.tahun_terbit,
        bukus.foto,
        bukus.stok,
        bukus.sold,
        bukus.harga,
        bukus.slug,
        bukus.created_at AS buku_created_at,
        bukus.updated_at AS buku_updated_at,
        addresses.id AS address_id,
        addresses.user_id AS address_user_id,
        addresses.selected_address_id,
        addresses.name AS address_name,
        addresses.penerima,
        addresses.no_penerima,
        addresses.provinsi,
        addresses.kota,
        addresses.kecamatan,
        addresses.alamat_lengkap,
        addresses.postal_code,
        addresses.label,
        addresses.created_at AS address_created_at,
        addresses.updated_at AS address_updated_at,
        payments.transaction_id AS payment_transaction_id,
        payments.mdtransaction_id AS payment_mdtransaction_id,
        payments.masked_card AS payment_masked_card,
        payments.payment_type AS payment_payment_type,
        payments.transaction_time AS payment_transaction_time,
        payments.bank AS payment_bank,
        payments.gross_amount AS payment_gross_amount,
        payments.card_type AS payment_card_type,
        payments.payment_option_type AS payment_option_type,
        payments.shopeepay_reference_number AS payment_shopeepay_reference_number,
        payments.reference_id AS payment_reference_id,
        payments.link AS payment_link,
        shipments.bsorder_id AS shipment_bsorder_id,
        shipments.shipping_cost AS shipment_shipping_cost,
        shipments.waybill_id AS shipment_waybill_id,
        shipments.courier_details AS shipment_courier_details
    FROM orders
    LEFT JOIN items ON items.order_id = orders.id
    LEFT JOIN bukus ON bukus.id = items.buku_id
    LEFT JOIN addresses ON orders.address_id = addresses.id
    LEFT JOIN payments ON payments.order_id = orders.id
    LEFT JOIN shipments ON shipments.order_id = orders.id
    WHERE payments.transaction_id = :transactionId
";

        $results = DB::select($query, ['transactionId' => $transaction_id]);

        if (empty($results)) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $courierDetails = json_decode($results[0]->shipment_courier_details, true) ?? [];

        $order = [
            'id' => $results[0]->order_id,
            'user_id' => $results[0]->user_id,
            'address_id' => $results[0]->address_id,
            'transaction_id' => $results[0]->payment_transaction_id,
            'bsorder_id' => $results[0]->shipment_bsorder_id,
            'waybill_id' => $results[0]->shipment_waybill_id,
            'total_payment' => $results[0]->total_payment,
            'shipping_cost' => $results[0]->shipment_shipping_cost,
            'status' => $results[0]->status,
            'created_at' => $results[0]->order_created_at,
            'updated_at' => $results[0]->order_updated_at,
            'courier_details' => $courierDetails,
            'items' => [],
            'address' => [
                'id' => $results[0]->address_id,
                'user_id' => $results[0]->address_user_id,
                'selected_address_id' => $results[0]->selected_address_id,
                'name' => $results[0]->address_name,
                'penerima' => $results[0]->penerima,
                'no_penerima' => $results[0]->no_penerima,
                'provinsi' => $results[0]->provinsi,
                'kota' => $results[0]->kota,
                'kecamatan' => $results[0]->kecamatan,
                'alamat_lengkap' => $results[0]->alamat_lengkap,
                'postal_code' => $results[0]->postal_code,
                'label' => $results[0]->label,
                'created_at' => $results[0]->address_created_at,
                'updated_at' => $results[0]->address_updated_at
            ],
            'payment' => [
                'transaction_id' => $results[0]->payment_transaction_id,
                'mdtransaction_id' => $results[0]->payment_mdtransaction_id,
                'masked_card' => $results[0]->payment_masked_card,
                'payment_type' => $results[0]->payment_payment_type,
                'transaction_time' => $results[0]->payment_transaction_time,
                'bank' => $results[0]->payment_bank,
                'gross_amount' => $results[0]->payment_gross_amount,
                'card_type' => $results[0]->payment_card_type,
                'payment_option_type' => $results[0]->payment_option_type,
                'shopeepay_reference_number' => $results[0]->payment_shopeepay_reference_number,
                'reference_id' => $results[0]->payment_reference_id,
                'link' => $results[0]->payment_link,
            ]
        ];

        foreach ($results as $row) {
            $reviews = Review::where('buku_id', $row->buku_id)
                ->where('order_id', $row->order_id)
                ->when(!$isAdmin, function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'buku_id' => $review->buku_id,
                        'user_id' => $review->user_id,
                        'order_id' => $review->order_id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at
                    ];
                })
                ->toArray();

            $order['items'][] = [
                'id' => $row->item_id,
                'order_id' => $row->order_id,
                'buku_id' => $row->buku_id,
                'quantity' => $row->quantity,
                'price' => $row->item_price,
                'created_at' => $row->item_created_at,
                'updated_at' => $row->item_updated_at,
                'buku' => [
                    'id' => $row->buku_id,
                    'no_isbn' => $row->no_isbn,
                    'judul' => $row->judul,
                    'desc' => $row->desc,
                    'pengarang' => $row->pengarang,
                    'penerbit' => $row->penerbit,
                    'tahun_terbit' => $row->tahun_terbit,
                    'foto' => asset('storage/buku_photos/' . basename($row->foto)),
                    'stok' => $row->stok,
                    'sold' => $row->sold,
                    'harga' => $row->item_price / $row->quantity,
                    'slug' => $row->slug,
                    'created_at' => $row->buku_created_at,
                    'updated_at' => $row->buku_updated_at,
                    'reviews' => $reviews
                ]
            ];
        }

        return response()->json($order);
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
                $order->status = 'packing';
                $order->save();

                $shipment = Shipment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'bsorder_id' => $response['id'],
                        'waybill_id' => $response['courier']['waybill_id'],
                    ]
                );

                foreach ($request->item_ids as $item) {
                    $buku = Buku::findOrFail($item['item_id']);
                    $buku->stok -= $item['quantity'];
                    $buku->sold += $item['quantity'];
                    $buku->save();

                    $orderItem = Item::where('order_id', $order->id)
                        ->where('buku_id', $buku->id)
                        ->first();

                    if ($orderItem) {
                        $orderItem->quantity = $item['quantity'];
                        $orderItem->save();
                    } else {
                        Item::create([
                            'order_id' => $order->id,
                            'buku_id' => $buku->id,
                            'quantity' => $item['quantity'],
                            'price' => $buku->harga
                        ]);
                    }
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

    public function cancelOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            if ($order->status != 'pending') {
                return response()->json(['message' => 'Order cannot be cancelled'], 400);
            }

            $order->status = 'cancelled';
            $order->save();

            $payment = Payment::where('order_id', $orderId)->first();

            if ($payment) {
                $payment->link = null;
                $payment->save();
            }

            return response()->json(['message' => 'Order cancelled successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error cancelling order', 'error' => $e->getMessage()], 500);
        }
    }

    // public function biteshipWebhook(Request $request)
    // {
    //     $payload = $request->all();
    //     try {
    //         if (isset($payload['order_id']) && isset($payload['status'])) {
    //             $shipment = Shipment::where('bsorder_id', $payload['order_id'])->firstOrFail();

    //             $order = Order::findOrFail($shipment->order_id);
    //             $newStatus = $payload['status'];

    //             if ($newStatus === 'dropping_off') {
    //                 $order->status = 'delivery';
    //             } elseif ($newStatus === 'delivered') {
    //                 $order->status = 'delivered';
    //             } else {
    //                 $order->status = $newStatus;
    //             }

    //             $order->save();

    //             return response()->json(['message' => 'Webhook processed successfully'], 200);
    //         } else {
    //             Log::error('Invalid payload received', ['payload' => $payload]);
    //             return response()->json(['message' => 'Invalid payload', 'payload' => $payload], 200);
    //         }
    //     } catch (Exception $e) {
    //         Log::error('Error processing webhook', ['error' => $e->getMessage(), 'payload' => $payload]);
    //         return response()->json(['message' => 'Error processing webhook', 'error' => $e->getMessage()], 200);
    //     }
    // }

    public function biteshipWebhook(Request $request)
    {
        $payload = $request->all();
        try {
            if (isset($payload['order_id']) && isset($payload['status'])) {
                $shipment = Shipment::where('bsorder_id', $payload['order_id'])->firstOrFail();
                $order = Order::findOrFail($shipment->order_id);
                $newStatus = $payload['status'];


                $packingStatuses = [
                    'confirmed',
                    'allocated',
                    'picking_up',
                    'picked'
                ];

                $deliveryStatuses = [
                    'dropping_off',
                    'return_in_transit',
                    'on_hold'
                ];
                if (in_array($newStatus, $packingStatuses)) {
                    $order->status = 'packing';
                } elseif (in_array($newStatus, $deliveryStatuses)) {
                    $order->status = 'delivery';
                } elseif ($newStatus === 'delivered') {
                    $order->status = 'delivery';
                } else {
                    $order->status = $newStatus;
                }

                $order->save();

                return response()->json(['message' => 'Webhook processed successfully'], 200);
            } else {
                Log::error('Invalid payload received', ['payload' => $payload]);
                return response()->json(['message' => 'Invalid payload', 'payload' => $payload], 200);
            }
        } catch (Exception $e) {
            Log::error('Error processing webhook', ['error' => $e->getMessage(), 'payload' => $payload]);
            return response()->json(['message' => 'Error processing webhook', 'error' => $e->getMessage()], 200);
        }
    }

    public function midtransWebhook(Request $request)
    {
        $payload = $request->all();
        try {
            if (isset($payload['order_id'])) {
                $orderId = $payload['order_id'];
                $transactionStatus = $payload['transaction_status'];

                $payment = Payment::where('transaction_id', $orderId)->firstOrFail();

                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    // Update payment data
                    $payment->update([
                        'masked_card' => $payload['masked_card'] ?? $payment->masked_card,
                        'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                        'transaction_time' => $payload['transaction_time'] ?? $payment->transaction_time,
                        'bank' => $payload['bank'] ?? $payment->bank,
                        'gross_amount' => $payload['gross_amount'] ?? $payment->gross_amount,
                        'card_type' => $payload['card_type'] ?? $payment->card_type,
                        'mdtransaction_id' => $payload['transaction_id'] ?? $payment->mdtransaction_id,
                    ]);

                    // Update order status
                    $order = Order::find($payment->order_id);
                    if ($order) {
                        $order->status = 'process';
                        $order->save();
                    }
                } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                    // Handle other transaction statuses
                    $order = Order::find($payment->order_id);
                    if ($order) {
                        $order->status = 'failed';
                        $order->save();
                    }
                }

                return response()->json(['message' => 'Webhook processed successfully'], 200);
            } else {
                Log::error('Invalid payload received', ['payload' => $payload]);
                return response()->json(['message' => 'Invalid payload', 'payload' => $payload], 200);
            }
        } catch (Exception $e) {
            Log::error('Error processing webhook', ['error' => $e->getMessage(), 'payload' => $payload]);
            return response()->json(['message' => 'Error processing webhook', 'error' => $e->getMessage()], 200);
        }
    }
}
