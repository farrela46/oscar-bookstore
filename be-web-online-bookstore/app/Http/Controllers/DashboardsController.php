<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Buku;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardsController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', null);

        $startDate = Carbon::createFromDate($year, $month ?: 1, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month ?: 12, 31)->endOfMonth();

        $totalTransactions = Order::where('status', 'finished')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_payment');

        $totalItemsSold = Order::where('status', 'finished')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->reduce(function ($carry, $order) {
                $items = json_decode($order->items, true);
                return $carry + array_reduce($items, function ($sum, $item) {
                    return $sum + $item['quantity'];
                }, 0);
            }, 0);

        $totalBooksSold = Buku::whereBetween('updated_at', [$startDate, $endDate])
            ->sum('sold');

        $totalUsers = User::where('role', 'USER')->count();

        $totalProducts = Buku::count();

        return response()->json([
            'total_transactions' => $totalTransactions,
            'total_items_sold' => $totalItemsSold,
            'total_books_sold' => $totalBooksSold,
            'total_users' => $totalUsers,
            'total_products' => $totalProducts
        ]);
    }

    public function getBookStatistics()
    {
        // Mendapatkan data buku yang diurutkan berdasarkan jumlah penjualan terbanyak
        $bukus = Buku::orderBy('sold', 'desc')->get(['judul', 'sold', 'harga', 'foto']);

        // Membuat array untuk menyimpan statistik buku
        $bookStatistics = $bukus->map(function ($buku) {
            return [
                'judul' => $buku->judul,
                'sales' => $buku->sold,
                'value' => $buku->harga * $buku->sold, 2,
                'foto' => $buku->foto, 
            ];
        });

        return response()->json($bookStatistics);
    }





}
