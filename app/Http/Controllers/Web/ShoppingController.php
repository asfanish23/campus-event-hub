<?php

namespace App\Http\Controllers\Web;

use App\Models\Product;
use App\Models\Club;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShoppingController extends Controller
{
    /**
     * Display all merchandise/products from all clubs
     */
    public function index(Request $request)
    {
        $query = Product::with(['club', 'media', 'variants']);

        // Filter by club if specified
        if ($request->filled('club')) {
            $query->where('club_id', $request->club);
        }

        // Filter by category if specified
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search by product name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12);
        $clubs = Club::all();
        $categories = Product::distinct('category')->pluck('category')->filter();

        return view('shopping.index', compact('products', 'clubs', 'categories'));
    }

    /**
     * Display product details
     */
    public function show(Product $product)
    {
        $product->load(['club', 'media', 'variants']);
        $relatedProducts = Product::where('club_id', $product->club_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('shopping.show', compact('product', 'relatedProducts'));
    }
}
