<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return view('customer.dashboard', [
            'currentOrder' => Order::with(['items.product', 'shipment'])->where('user_id', $user->id)->latest()->first(),
            'orders' => Order::where('user_id', $user->id)->latest()->take(8)->get(),
            'wishlist' => Wishlist::with('product')->where('user_id', $user->id)->latest()->take(2)->get(),
            'tickets' => SupportTicket::where('user_id', $user->id)->latest()->take(4)->get(),
        ]);
    }
}
