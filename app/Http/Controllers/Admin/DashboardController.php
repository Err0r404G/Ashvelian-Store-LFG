<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'roleCounts' => User::select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role'),
            'activeCustomers' => User::where('role', 'customer')->where('is_restricted', false)->count(),
            'ordersToday' => Order::whereDate('placed_at', today())->count(),
            'monthlyRevenue' => Order::whereMonth('placed_at', now()->month)->whereYear('placed_at', now()->year)->sum('grand_total'),
            'bestSellers' => OrderItem::select('product_name', DB::raw('sum(quantity) as sales'), DB::raw('sum(line_total) as revenue'))
                ->groupBy('product_name')
                ->orderByDesc('sales')
                ->take(5)
                ->get(),
            'lowStockProducts' => Product::with('category')->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->take(4)->get(),
            'team' => User::whereIn('role', ['admin', 'manager', 'delivery_manager'])->latest()->take(6)->get(),
            'activityLogs' => ActivityLog::with('user')->latest()->take(8)->get(),
        ]);
    }

    public function toggleRestriction(User $user)
    {
        abort_unless($user->role === 'customer', 422);

        $user->update(['is_restricted' => ! $user->is_restricted]);

        return back()->with('status', $user->is_restricted ? 'Customer restricted.' : 'Customer unrestricted.');
    }
}
