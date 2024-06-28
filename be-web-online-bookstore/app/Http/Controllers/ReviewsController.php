<?php

namespace App\Http\Controllers;

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

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully',
            'data' => $review
        ], 201);
    }

    public function index($bukuId)
    {
        $reviews = Review::where('buku_id', $bukuId)->with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }
}
