<?php
namespace App\Http\Controllers\Api;

use App\Models\CartItem;
use App\Http\Resources\CartItemResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartItemController extends BaseController
{
    public function index(Request $request)
    {
        $items = CartItem::with(['product'])
            ->where('user_id', auth()->id())
            ->paginate($request->get('per_page', 50));

        return $this->sendPaginated(
            $items,
            CartItemResource::collection($items),
            'Cart items retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'        => 'required|exists:products,id',
            'quantity'          => 'sometimes|integer|min:1',
            'rental_start_date' => 'nullable|date',
            'rental_end_date'   => 'nullable|date|after:rental_start_date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $userId   = auth()->id();
        $itemType = ($request->rental_start_date || $request->type === 'rental') ? 'rental' : 'purchase';

        $existing = CartItem::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->where('item_type', $itemType)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $request->quantity ?? 1);
            return $this->sendResponse(
                new CartItemResource($existing->load('product')),
                'Cart item quantity updated'
            );
        }

        $item = CartItem::create([
            'user_id'           => $userId,
            'product_id'        => $request->product_id,
            'quantity'          => $request->quantity ?? 1,
            'item_type'         => $itemType,
            'rental_start_date' => $request->rental_start_date,
            'rental_end_date'   => $request->rental_end_date,
        ]);

        return $this->sendResponse(
            new CartItemResource($item->load('product')),
            'Item added to cart successfully',
            201
        );
    }

    public function show($id)
    {
        $item = CartItem::with(['product'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$item) {
            return $this->sendError('Cart item not found', [], 404);
        }

        return $this->sendResponse(new CartItemResource($item), 'Cart item retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $item = CartItem::where('user_id', auth()->id())->find($id);

        if (!$item) {
            return $this->sendError('Cart item not found', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity'          => 'sometimes|integer|min:1',
            'rental_start_date' => 'nullable|date',
            'rental_end_date'   => 'nullable|date|after:rental_start_date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $item->update($request->only(['quantity', 'rental_start_date', 'rental_end_date']));

        return $this->sendResponse(new CartItemResource($item), 'Cart item updated successfully');
    }

    public function destroy($id)
    {
        $item = CartItem::where('user_id', auth()->id())->find($id);

        if (!$item) {
            return $this->sendError('Cart item not found', [], 404);
        }

        $item->delete();
        return $this->sendResponse(null, 'Item removed from cart successfully');
    }

    public function clear($user_id)
    {
        CartItem::where('user_id', auth()->id())->delete();
        return $this->sendResponse(null, 'Cart cleared successfully');
    }
}
