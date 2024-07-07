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
    // public function getDashboardData(Request $request)
    // {
    //     $year = $request->input('year', Carbon::now()->year);
    //     $month = $request->input('month', null);

    //     $startDate = Carbon::createFromDate($year, $month ?: 1, 1)->startOfMonth();
    //     $endDate = Carbon::createFromDate($year, $month ?: 12, 31)->endOfMonth();

    //     $totalTransactions = Order::where('status', 'finished')
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->sum('total_payment');

    //     $totalItemsSold = Order::where('status', 'finished')
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->get()
    //         ->reduce(function ($carry, $order) {
    //             $items = json_decode($order->items, true);
    //             return $carry + array_reduce($items, function ($sum, $item) {
    //                 return $sum + $item['quantity'];
    //             }, 0);
    //         }, 0);

    //     $totalBooksSold = Buku::whereBetween('updated_at', [$startDate, $endDate])
    //         ->sum('sold');

    //     $totalUsers = User::where('role', 'USER')->count();

    //     $totalProducts = Buku::count();

    //     return response()->json([
    //         'total_transactions' => $totalTransactions,
    //         'total_items_sold' => $totalItemsSold,
    //         'total_books_sold' => $totalBooksSold,
    //         'total_users' => $totalUsers,
    //         'total_products' => $totalProducts
    //     ]);
    // }

    public function getDashboardData(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', null);

        if ($month) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        } else {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        }

        $totalTransactions = Order::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_payment');

        // Total Items Sold
        $totalItemsSold = DB::table('items')
            ->join('orders', 'items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum('items.quantity');

        // Monthly Sales Data
        $monthlySales = Order::selectRaw('MONTH(created_at) as month, SUM(total_payment) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month')
            ->toArray();

        $monthlySalesData = array_fill(1, 12, 0);
        foreach ($monthlySales as $monthNumber => $total) {
            $monthlySalesData[$monthNumber] = $total;
        }

        // Monthly Book Sales Data
        $monthlyBookSales = DB::table('items')
            ->selectRaw('MONTH(orders.created_at) as month, SUM(items.quantity) as total')
            ->join('orders', 'items.order_id', '=', 'orders.id')
            ->whereYear('orders.created_at', $year)
            ->groupByRaw('MONTH(orders.created_at)')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyBookSalesData = array_fill(1, 12, 0);
        foreach ($monthlyBookSales as $monthNumber => $total) {
            $monthlyBookSalesData[$monthNumber] = $total;
        }

        if ($month) {
            $monthlySalesData = array_fill(1, 12, 0);
            $monthlyBookSalesData = array_fill(1, 12, 0);
            $monthlySalesData[$month] = $monthlySales[$month] ?? 0;
            $monthlyBookSalesData[$month] = $monthlyBookSales[$month] ?? 0;
        } else {
            $monthlyBookSalesData = array_values($monthlyBookSalesData);
        }

        $totalUsers = User::where('role', 'USER')->count();

        $totalProducts = Buku::count();

        return response()->json([
            'total_transactions' => $totalTransactions,
            'total_items_sold' => $totalItemsSold,
            'total_books_sold' => array_sum($monthlyBookSalesData),
            'total_users' => $totalUsers,
            'total_products' => $totalProducts,
            'monthly_sales' => array_values($monthlySalesData),
            'monthly_book_sales' => array_values($monthlyBookSalesData),
        ]);
    }





    public function getBookStatistics()
    {
        $bukus = Buku::orderBy('sold', 'desc')->get(['judul', 'sold', 'harga', 'foto']);

        $bookStatistics = $bukus->map(function ($buku) {
            return [
                'judul' => $buku->judul,
                'sales' => $buku->sold,
                'value' => $buku->harga * $buku->sold,
                2,
                'foto' => asset('storage/buku_photos/' . basename($buku->foto)),
            ];
        });

        return response()->json($bookStatistics);
    }





}
