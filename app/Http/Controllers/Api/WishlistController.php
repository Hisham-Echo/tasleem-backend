<?php
namespace App\Http\Controllers\Api;

use App\Models\Wishlist;
use App\Http\Resources\WishlistResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends BaseController
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $wishlist = Wishlist::with('product')
            ->where('user_id', $userId)
            ->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $wishlist,
            WishlistResource::collection($wishlist),
            'Wishlist retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $userId = auth()->id();

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return $this->sendError('Product already in wishlist', [], 400);
        }

        $wishlist = Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $request->product_id,
        ]);

        return $this->sendResponse(
            new WishlistResource($wishlist->load('product')),
            'Product added to wishlist successfully',
            201
        );
    }

    public function destroy($id)
    {
        $wishlist = Wishlist::where('user_id', auth()->id())->find($id);

        if (!$wishlist) {
            return $this->sendError('Wishlist item not found', [], 404);
        }

        $wishlist->delete();

        return $this->sendResponse(null, 'Product removed from wishlist successfully');
    }

    public function clear($userId)
    {
        Wishlist::where('user_id', auth()->id())->delete();
        return $this->sendResponse(null, 'Wishlist cleared successfully');
    }

    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->exists();

        $item = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        return $this->sendResponse([
            'in_wishlist'      => $exists,
            'wishlist_item_id' => $item?->id ?? $item?->wishlist_id ?? null,
        ], 'Check completed successfully');
    }
}
