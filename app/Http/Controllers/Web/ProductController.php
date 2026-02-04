<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct()
    {
        // Only club admins can access merchandise management
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'super_admin'])) {
                return redirect('/student/dashboard')->with('error', 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $products = Product::where('club_id', $user->club_id)->with('media')->get();
        $totalProducts = Product::where('club_id', $user->club_id)->count();
        $totalSales = Order::whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'))->sum('total') ?? 0;
        $itemsSold = Order::whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'))->sum('quantity') ?? 0;
        $pendingOrders = Order::whereIn('product_id', Product::where('club_id', $user->club_id)->pluck('id'))->where('status', 'Pending')->count();

        return view('merchandise.index', compact('products', 'totalProducts', 'totalSales', 'itemsSold', 'pendingOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('merchandise.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,mkv|max:51200'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            ...$validated,
            'image' => $imagePath,
            'club_id' => Auth::user()->club_id
        ]);

        // Handle multiple media files
        if ($request->hasFile('media_files')) {
            $order = 0;
            foreach ($request->file('media_files') as $file) {
                $fileType = in_array($file->getClientOriginalExtension(), ['mp4', 'mov', 'avi', 'mkv']) ? 'video' : 'photo';
                $filePath = $file->store('product-media', 'public');
                
                ProductMedia::create([
                    'product_id' => $product->id,
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                    'order' => $order++
                ]);
            }
        }

        return redirect()->route('merchandise.index')->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($merchandise)
    {
        $product = Product::findOrFail($merchandise);
        return view('merchandise.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($merchandise)
    {
        $product = Product::findOrFail($merchandise);
        return view('merchandise.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $merchandise)
    {
        $product = Product::findOrFail($merchandise);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'media_files' => 'nullable|array',
            'media_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,mkv|max:51200',
            'deleted_media_ids' => 'nullable|string',
            'featured_media_id' => 'nullable|integer'
        ]);
        $featuredMediaId = $validated['featured_media_id'] ?? null;
        unset($validated['featured_media_id']);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && \Storage::disk('public')->exists($product->image)) {
                \Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product->update($validated);

        // Delete selected media files
        $deletedMediaIdsString = $validated['deleted_media_ids'] ?? '';
        \Log::info('DELETION_START', ['deleted_ids' => $deletedMediaIdsString]);
        
        if (!empty($deletedMediaIdsString)) {
            // Parse comma-separated IDs
            $deleteMediaIds = array_filter(array_map('intval', explode(',', $deletedMediaIdsString)));
            \Log::info('PARSED_IDS', ['ids' => $deleteMediaIds]);
            
            foreach ($deleteMediaIds as $mediaId) {
                $media = ProductMedia::find($mediaId);
                \Log::info('FINDING_MEDIA', ['mediaId' => $mediaId, 'found' => $media ? true : false]);
                
                if ($media) {
                    \Log::info('DELETING_MEDIA', ['mediaId' => $mediaId, 'path' => $media->file_path]);
                    if (\Storage::disk('public')->exists($media->file_path)) {
                        \Storage::disk('public')->delete($media->file_path);
                    }
                    $media->delete();
                    \Log::info('DELETED_MEDIA', ['mediaId' => $mediaId]);
                }
            }
        }
        
        // Refresh the product model to get updated media count
        $product->load('media');
        
        // Clear legacy image field if all media is deleted
        if ($product->media()->count() === 0) {
            $product->image = null;
            $product->save();
        }
        
        \Log::info('AFTER_DELETE', ['remaining_media' => $product->media()->count()]);

        // Handle new media files
        if ($request->hasFile('media_files')) {
            $order = $product->media()->max('order') ?? 0;
            foreach ($request->file('media_files') as $file) {
                $fileType = in_array($file->getClientOriginalExtension(), ['mp4', 'mov', 'avi', 'mkv']) ? 'video' : 'photo';
                $filePath = $file->store('product-media', 'public');
                
                ProductMedia::create([
                    'product_id' => $product->id,
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                    'order' => ++$order
                ]);
            }
        }

        // Apply featured media ordering (cover image)
        if (!empty($featuredMediaId)) {
            $mediaItems = $product->media()->orderBy('order')->get();
            $featured = $mediaItems->firstWhere('id', (int) $featuredMediaId);
            if ($featured) {
                $currentOrder = 0;
                $featured->order = $currentOrder++;
                $featured->save();
                foreach ($mediaItems as $media) {
                    if ($media->id === $featured->id) {
                        continue;
                    }
                    $media->order = $currentOrder++;
                    $media->save();
                }
            }
        }

        return redirect()->route('merchandise.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($merchandise)
    {
        $product = Product::findOrFail($merchandise);
        
        // Delete image
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('merchandise.index')->with('success', 'Product deleted successfully!');
    }
}
