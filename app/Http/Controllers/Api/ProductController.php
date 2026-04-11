<?php
namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'owner', 'images']);

        if ($request->has('category_id'))  $query->where('category_id', $request->category_id);
        if ($request->has('owner_id'))     $query->where('owner_id', $request->owner_id);
        if ($request->has('status'))       $query->where('status', $request->status);
        if ($request->has('type'))         $query->where('type', $request->type);
        if ($request->has('is_rentable') && $request->is_rentable) {
            $query->whereIn('type', ['rental', 'both']);
        }
        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%");
            });
        }
        if ($request->has('min_price')) $query->where('price', '>=', $request->min_price);
        if ($request->has('max_price')) $query->where('price', '<=', $request->max_price);

        $allowed  = ['id','name','price','created_at','view_count','rate','pay_count','quantity'];
        $sortBy   = in_array($request->get('sort_by'), $allowed) ? $request->get('sort_by') : 'created_at';
        $sortDir  = in_array(strtolower($request->get('sort_order','desc')), ['asc','desc'])
                    ? strtolower($request->get('sort_order','desc')) : 'desc';

        $products = $query->orderBy($sortBy, $sortDir)
                          ->paginate($request->get('per_page', 15));

        return $this->sendPaginated($products, ProductResource::collection($products), 'Products retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,category_id',
            'quantity'    => 'required|integer|min:1',
            // type accepts sale/rental/both OR is_rentable boolean
            'type'        => 'sometimes|in:sale,rental,both',
            'is_rentable' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Determine type from is_rentable if not sent explicitly
        $type = $request->type;
        if (!$type) {
            $type = $request->boolean('is_rentable') ? 'rental' : 'sale';
        }

        $product = Product::create([
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'category_id' => $request->category_id,
            'owner_id'    => auth()->id(), // ✅ from token, not request body
            'quantity'    => $request->quantity,
            'status'      => '1',          // active by default
            'type'        => $type,
            'view_count'  => 0,
            'rate'        => 0,
            'pay_count'   => 0,
            'addingToCart_count' => 0,
        ]);

        return $this->sendResponse(
            new ProductResource($product->load(['category', 'owner'])),
            'Product created successfully',
            201
        );
    }

    public function show($id)
    {
        $product = Product::with(['category', 'owner', 'images', 'reviews.user'])->find($id);

        if (!$product) return $this->sendError('Product not found', [], 404);

        $product->increment('view_count');

        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) return $this->sendError('Product not found', [], 404);

        if ($product->owner_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,category_id',
            'quantity'    => 'sometimes|integer|min:0',
            'type'        => 'sometimes|in:sale,rental,both',
            'is_rentable' => 'sometimes|boolean',
            'status'      => 'sometimes|in:1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $data = $request->only(['name','description','price','category_id','quantity','status']);

        if ($request->has('type')) {
            $data['type'] = $request->type;
        } elseif ($request->has('is_rentable')) {
            $data['type'] = $request->boolean('is_rentable') ? 'rental' : 'sale';
        }

        $product->update($data);

        return $this->sendResponse(new ProductResource($product->load(['category','owner'])), 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) return $this->sendError('Product not found', [], 404);

        if ($product->owner_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $product->delete();

        return $this->sendResponse(null, 'Product deleted successfully');
    }

    public function similar($id)
    {
        $product = Product::find($id);

        if (!$product) return $this->sendError('Product not found', [], 404);

        $similar = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', '1')
            ->with(['category', 'images'])
            ->limit(6)
            ->get();

        return $this->sendResponse(ProductResource::collection($similar), 'Similar products retrieved successfully');
    }
}
