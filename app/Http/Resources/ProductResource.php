<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        // Get first image URL for the `image` shortcut the frontend uses
        $images       = $this->whenLoaded('images');
        $primaryImage = $images && count($images) > 0 ? $images[0]->image_url : null;

        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'description'         => $this->description,
            'price'               => (float) $this->price,
            'old_price'           => null,
            'stock'               => $this->quantity,
            'quantity'            => $this->quantity,
            'condition'           => 'used',
            // Frontend uses is_rentable boolean
            'is_rentable'         => in_array($this->type, ['rental', 'both']),
            'type'                => $this->type,
            'status'              => $this->status,
            // Primary image shortcut
            'image'               => $primaryImage,
            'images'              => ProductImageResource::collection($this->whenLoaded('images')),
            // Category as object with `id`
            'category_id'         => $this->category_id,
            'category'            => new CategoryResource($this->whenLoaded('category')),
            // Seller
            'user_id'             => $this->owner_id,
            'seller'              => new UserResource($this->whenLoaded('owner')),
            // Stats
            'rating'              => (float) $this->rate,
            'reviews_count'       => $this->reviews()->count(),
            'views_count'         => $this->view_count,
            'pay_count'           => $this->pay_count,
            'addingToCart_count'  => $this->addingToCart_count,
            'created_at'          => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'          => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
