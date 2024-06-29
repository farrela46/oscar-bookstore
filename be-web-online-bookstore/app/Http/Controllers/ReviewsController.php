<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
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


    public function getReviewBook($buku_id)
    {
        $reviews = Review::where('buku_id', $buku_id)
            ->with('user') 
            ->get();

        // Return reviews with user information
        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}
