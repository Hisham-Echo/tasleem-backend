<?php
// app/Http/Controllers/Api/AiRecommendationController.php

namespace App\Http\Controllers\Api;

use App\Models\AiRecommendation;
use App\Http\Resources\AiRecommendationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiRecommendationController extends BaseController
{
    public function index(Request $request)
    {
        $query = AiRecommendation::with(['user', 'product']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('algorithm_type')) {
            $query->where('algorithm_type', $request->algorithm_type);
        }

        // Get only valid recommendations
        $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });

        $recommendations = $query->orderBy('score', 'desc')
            ->paginate($request->get('per_page', 20));

        return $this->sendPaginated(
            $recommendations,
            AiRecommendationResource::collection($recommendations),
            'Recommendations retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'score' => 'required|numeric|min:0|max:1',
            'algorithm_type' => 'required|in:collaborative,content,hybrid,popularity,location',
            'reason' => 'nullable|string',
            'metadata' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $recommendation = AiRecommendation::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'product_id' => $request->product_id,
                'algorithm_type' => $request->algorithm_type,
            ],
            $request->all()
        );

        return $this->sendResponse(
            new AiRecommendationResource($recommendation),
            'Recommendation saved successfully',
            201
        );
    }

    public function show($id)
    {
        $recommendation = AiRecommendation::with(['user', 'product'])->find($id);

        if (!$recommendation) {
            return $this->sendError('Recommendation not found');
        }

        return $this->sendResponse(
            new AiRecommendationResource($recommendation),
            'Recommendation retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $recommendation = AiRecommendation::find($id);

        if (!$recommendation) {
            return $this->sendError('Recommendation not found');
        }

        $validator = Validator::make($request->all(), [
            'score' => 'sometimes|numeric|min:0|max:1',
            'reason' => 'nullable|string',
            'metadata' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $recommendation->update($request->all());

        return $this->sendResponse(
            new AiRecommendationResource($recommendation),
            'Recommendation updated successfully'
        );
    }

    public function destroy($id)
    {
        $recommendation = AiRecommendation::find($id);

        if (!$recommendation) {
            return $this->sendError('Recommendation not found');
        }

        $recommendation->delete();

        return $this->sendResponse(null, 'Recommendation deleted successfully');
    }
}