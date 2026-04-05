<?php
// app/Http/Controllers/Api/CartItemController.php

namespace App\Http\Controllers\Api;

use App\Models\CartItem;
use App\Http\Resources\CartItemResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartItemController extends BaseController
{
    public function index(Request $request)
    {
        $query = CartItem::with(['user', 'product']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $items = $query->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $items,
            CartItemResource::collection($items),
            'Cart items retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date|after:rental_start_date',
            'item_type' => 'required|in:purchase,rental',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Check if item already in cart
        $existing = CartItem::where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->where('item_type', $request->item_type)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $request->quantity);
            return $this->sendResponse(
                new CartItemResource($existing),
                'Cart item updated successfully'
            );
        }

        $item = CartItem::create($request->all());

        return $this->sendResponse(
            new CartItemResource($item),
            'Item added to cart successfully',
            201
        );
    }

    public function show($id)
    {
        $item = CartItem::with(['user', 'product'])->find($id);

        if (!$item) {
            return $this->sendError('Cart item not found');
        }

        return $this->sendResponse(
            new CartItemResource($item),
            'Cart item retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $item = CartItem::find($id);

        if (!$item) {
            return $this->sendError('Cart item not found');
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|integer|min:1',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date|after:rental_start_date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $item->update($request->all());

        return $this->sendResponse(
            new CartItemResource($item),
            'Cart item updated successfully'
        );
    }

    public function destroy($id)
    {
        $item = CartItem::find($id);

        if (!$item) {
            return $this->sendError('Cart item not found');
        }

        $item->delete();

        return $this->sendResponse(null, 'Item removed from cart successfully');
    }

    /**
     * Clear user's cart
     */
    public function clear($user_id)  
    {
       
        $user = \App\Models\User::find($user_id);
        
        if (!$user) {
            return $this->sendError('User not found', [], 404);
        }
        
        
        \App\Models\CartItem::where('user_id', $user_id)->delete();
        
        return $this->sendResponse(null, 'Cart cleared successfully');
    }
}