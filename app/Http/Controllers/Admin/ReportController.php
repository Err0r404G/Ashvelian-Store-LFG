<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $orders = Order::with('items')
            ->whereBetween('placed_at', [$start, $end])
            ->latest('placed_at')
            ->get();

        return view('admin.reports.index', [
            'month' => $month,
            'start' => $start,
            'end' => $end,
            'orders' => $orders,
            'totalUsers' => User::count(),
            'customers' => User::where('role', 'customer')->count(),
            'totalOrders' => $orders->count(),
            'totalRevenue' => $orders->sum('grand_total'),
            'totalDiscount' => $orders->sum('discount_total'),
            'bestSellers' => OrderItem::select('product_name', DB::raw('sum(quantity) as quantity'), DB::raw('sum(line_total) as revenue'))
                ->whereHas('order', fn ($query) => $query->whereBetween('placed_at', [$start, $end]))
                ->groupBy('product_name')
                ->orderByDesc('quantity')
                ->take(8)
                ->get(),
            'lowStockProducts' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->get(),
            'printMode' => $request->boolean('print'),
        ]);
    }
}
