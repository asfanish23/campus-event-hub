<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    private function formatCartItem(CartItem $cartItem): array
    {
        $price = (float) ($cartItem->product?->price ?? 0);

        return [
            'id' => $cartItem->id,
            'user_id' => $cartItem->user_id,
            'product_id' => $cartItem->product_id,
            'quantity' => $cartItem->quantity,
            'price' => $price,
            'total_price' => $price * $cartItem->quantity,
            'created_at' => $cartItem->created_at,
            'updated_at' => $cartItem->updated_at,
            'product' => $cartItem->product,
        ];
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized - Please login first',
            'error_code' => 'UNAUTHENTICATED'
        ], 401);
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Cart item not found',
            'error_code' => 'CART_ITEM_NOT_FOUND'
        ], 404);
    }

    /**
     * Add a product to cart.
     */
    public function add(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'sometimes|integer|min:1',
            ]);

            $productId = (int) $validated['product_id'];
            $quantity = (int) ($validated['quantity'] ?? 1);

            \Log::info('Add to cart request', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);

            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND'
                ], 404);
            }

            $cartItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
                \Log::info('Updated existing cart item', [
                    'cart_item_id' => $cartItem->id,
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $cartItem->quantity,
                ]);
            } else {
                $cartItem = CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
                \Log::info('Created cart item', [
                    'cart_item_id' => $cartItem->id,
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }

            $cartItem->load('product.media');

            return response()->json([
                'success' => true,
                'message' => 'Added to cart successfully',
                'data' => $this->formatCartItem($cartItem)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error adding to cart', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'product_id' => $request->input('product_id'),
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error_code' => 'ADD_CART_FAILED'
            ], 500);
        }
    }

    /**
     * Get current user's cart.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $items = CartItem::with('product.media')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedItems = $items->map(fn (CartItem $item) => $this->formatCartItem($item))->values();
            $cartTotal = $formattedItems->sum('total_price');

            \Log::info('Get cart request', [
                'user_id' => $user->id,
                'count' => $items->count(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'count' => $items->count(),
                'cart_total' => $cartTotal,
                'data' => $formattedItems
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching cart', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart',
                'error_code' => 'FETCH_CART_FAILED'
            ], 500);
        }
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(Request $request, int $cartItemId)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = CartItem::with('product.media')
                ->where('id', $cartItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse();
            }

            $cartItem->quantity = (int) $validated['quantity'];
            $cartItem->save();
            $cartItem->refresh()->load('product.media');

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $this->formatCartItem($cartItem),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating cart item', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'cart_item_id' => $cartItemId,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error_code' => 'UPDATE_CART_FAILED'
            ], 500);
        }
    }

    /**
     * Remove an item from cart.
     */
    public function remove(int $cartItemId)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $cartItem = CartItem::where('id', $cartItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse();
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart item removed successfully',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error removing cart item', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'cart_item_id' => $cartItemId,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove cart item',
                'error_code' => 'REMOVE_CART_FAILED'
            ], 500);
        }
    }
}
