<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        // Get orders only for products from the club admin's club
        $query = Order::with('product')
            ->whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'));

        // Filter by status
        if ($request->get('status') && $request->get('status') !== '') {
            $query->where('status', $request->get('status'));
        }

        $orders = $query->orderBy('date', 'desc')->get();
        $totalOrders = Order::whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'))->count();
        $pendingOrders = Order::whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'))->where('status', 'pending')->count();
        $completedOrders = Order::whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'))->where('status', 'completed')->count();
        $totalRevenue = Order::whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'))->sum('total') ?? 0;

        return view('orders.index', compact('orders', 'totalOrders', 'pendingOrders', 'completedOrders', 'totalRevenue'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,processed,ready,completed,cancelled'
        ]);

        $order->update($validated);

        return redirect()->route('orders.index')->with('success', 'Order status updated successfully!');
    }
}
