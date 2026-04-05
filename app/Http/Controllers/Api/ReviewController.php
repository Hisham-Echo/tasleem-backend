<?php
// app/Http/Controllers/Api/ReviewController.php

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

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
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
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Check if user already reviewed this product
        $existing = Review::where('product_id', $request->product_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existing) {
            return $this->sendError('User already reviewed this product');
        }

        $review = Review::create($request->all());

        return $this->sendResponse(
            new ReviewResource($review->load(['user', 'product'])),
            'Review created successfully',
            201
        );
    }

    public function show($id)
    {
        $review = Review::with(['user', 'product'])->find($id);

        if (!$review) {
            return $this->sendError('Review not found');
        }

        return $this->sendResponse(
            new ReviewResource($review),
            'Review retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->sendError('Review not found');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $review->update($request->only(['rating', 'comment']));

        return $this->sendResponse(
            new ReviewResource($review),
            'Review updated successfully'
        );
    }

    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->sendError('Review not found');
        }

        $review->delete();

        return $this->sendResponse(null, 'Review deleted successfully');
    }
}