<?php

namespace App\Http\Controllers;

use Exception;
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
        $orderDetails = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => $request->input('amount'),
            ],
            'customer_details' => [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ],
        ];

        try {
            $paymentUrl = $this->midtransService->createTransaction($orderDetails);
            return redirect($paymentUrl);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
