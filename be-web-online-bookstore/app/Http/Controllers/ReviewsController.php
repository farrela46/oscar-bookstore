<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'buku_id' => 'required|exists:bukus,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string'
        ]);

        $review = Review::create($validated);
        $order = Order::findOrFail($validated['order_id']);
        $order->status = 'finished';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully',
            'data' => $review
        ], 201);
    }


    public function getAllReviews()
    {
        $reviews = Review::with(['user', 'buku'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

}
