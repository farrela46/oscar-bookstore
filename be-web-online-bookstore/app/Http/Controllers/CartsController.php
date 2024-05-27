<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartsController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:bukus,id'
        ]);

        $user = Auth::user();
        $buku = Buku::findOrFail($request->buku_id);

        $cart = Cart::where('user_id', $user->id)->where('buku_id', $buku->id)->first();

        if ($cart) {
            $cart->quantity += 1;
            $cart->save();
        } else {
            Cart::create([
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'quantity' => 1
            ]);
        }

        return response()->json(['message' => 'Buku berhasil ditambahkan ke keranjang!'], 200);
    }


    public function viewCart()
    {
        $user = Auth::user();

        Cart::where('user_id', $user->id)->update(['selected' => false]);
        $cartItems = $user->carts()->with('buku')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Anda belum menambahkan barang'], 200);
        }

        $books = $cartItems->map(function ($cartItem) {
            return [

                'id' => $cartItem->id,
                'no_isbn' => $cartItem->buku->no_isbn,
                'judul' => $cartItem->buku->judul,
                'desc' => $cartItem->buku->desc,
                'pengarang' => $cartItem->buku->pengarang,
                'penerbit' => $cartItem->buku->penerbit,
                'tahun_terbit' => $cartItem->buku->tahun_terbit,
                'foto' => asset('storage/buku_photos/' . basename($cartItem->buku->foto)),
                'stok' => $cartItem->buku->stok,
                'harga' => $cartItem->buku->harga,
                'slug' => $cartItem->buku->slug,
                'created_at' => $cartItem->buku->created_at,
                'updated_at' => $cartItem->buku->updated_at,
                'quantity' => $cartItem->quantity,
                'totalPrice' => $cartItem->quantity * $cartItem->buku->harga
            ];
        });

        return response()->json(['data' => $books], 200);
    }

    public function updateCart(Request $request, $id)
    {
        $user = Auth::user();
        $cartItem = $user->carts()->where('id', $id)->first();

        if ($cartItem) {
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
            return response()->json(['success' => 'Cart updated successfully'], 200);
        } else {
            return response()->json(['error' => 'Cart item not found'], 404);
        }
    }
    public function updateSelected(Request $request)
    {
        $userId = Auth::id();

        Cart::where('user_id', $userId)->update(['selected' => false]);

        Cart::whereIn('id', $request->selected_ids)
            ->where('user_id', $userId)
            ->update(['selected' => true]);

        return response()->json(['message' => 'Selected items updated successfully']);
    }

    public function removeFromCart($id)
    {
        $cartItem = Cart::findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Buku berhasil dihapus dari keranjang!'], 200);
    }

    public function getCheckout()
    {
        $userId = Auth::id();
        $selectedItems = Cart::where('user_id', $userId)
            ->where('selected', true)
            ->with('buku')
            ->get();

        $totalPayment = 0;

        $formattedItems = $selectedItems->map(function ($item) use (&$totalPayment) {
            $itemTotalPrice = $item->quantity * $item->buku->harga;
            $totalPayment += $itemTotalPrice;

            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'buku_id' => $item->buku_id,
                'quantity' => $item->quantity,
                'selected' => $item->selected,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'totalPrice' => $itemTotalPrice,
                'buku' => [
                    'id' => $item->buku->id,
                    'no_isbn' => $item->buku->no_isbn,
                    'judul' => $item->buku->judul,
                    'desc' => $item->buku->desc,
                    'pengarang' => $item->buku->pengarang,
                    'penerbit' => $item->buku->penerbit,
                    'tahun_terbit' => $item->buku->tahun_terbit,
                    'foto' => asset('storage/buku_photos/' . basename($item->buku->foto)),
                    'stok' => $item->buku->stok,
                    'harga' => $item->buku->harga,
                    'slug' => $item->buku->slug,
                    'created_at' => $item->buku->created_at,
                    'updated_at' => $item->buku->updated_at,
                ]
            ];
        });

        return response()->json([
            'data' => $formattedItems,
            'totalPayment' => $totalPayment
        ], 200);
    }


}
