<?php
namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends BaseController
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->latest()->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $reviews,
            ReviewResource::collection($reviews),
            'Reviews retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $userId = auth()->id();

        $existing = Review::where('product_id', $request->product_id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return $this->sendError('You already reviewed this product');
        }

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id'    => $userId,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return $this->sendResponse(
            new ReviewResource($review->load(['user', 'product'])),
            'Review submitted successfully',
            201
        );
    }

    public function show($id)
    {
        $review = Review::with(['user', 'product'])->find($id);
        if (!$review) return $this->sendError('Review not found', [], 404);
        return $this->sendResponse(new ReviewResource($review), 'Review retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $review = Review::where('user_id', auth()->id())->find($id);

        if (!$review) {
            return $this->sendError('Review not found or unauthorized', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating'  => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $review->update($request->only(['rating', 'comment']));
        return $this->sendResponse(new ReviewResource($review), 'Review updated successfully');
    }

    public function destroy($id)
    {
        $review = Review::where('user_id', auth()->id())->find($id);
        if (!$review) return $this->sendError('Review not found or unauthorized', [], 404);
        $review->delete();
        return $this->sendResponse(null, 'Review deleted successfully');
    }
}
