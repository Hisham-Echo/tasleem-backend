<?php
// app/Http/Controllers/Api/AiRecommendationController.php
// Proxies requests to the Python AI microservice, enriches with full product data

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiRecommendationController extends BaseController
{
    private function aiUrl(string $path): string
    {
        $base = rtrim(env('AI_SERVICE_URL', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }

    // Fetch product IDs from Python service, return full product objects
    private function enrichProducts(array $ids, int $limit = 20): array
    {
        if (empty($ids)) return [];

        $ids = array_slice($ids, 0, $limit);

        $products = Product::whereIn('id', $ids)
            ->where('status', '1')
            ->get()
            ->keyBy('id');

        // Preserve the AI-ranked order
        return collect($ids)
            ->map(fn($id) => $products[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();
    }

    // GET /api/v1/recommendations?last_product_id=X
    // Personalized recommendations for the logged-in user
    public function index(Request $request)
    {
        try {
            $userId = auth()->id();
            $params = array_filter([
                'last_product_id' => $request->last_product_id,
                'k' => 8,
            ]);

            $res = Http::timeout(10)->get($this->aiUrl("/recommend/user/{$userId}"), $params);

            if ($res->failed()) throw new \Exception("AI service error");

            $data     = $res->json();
            $products = $this->enrichProducts($data['ids'] ?? []);

            return response()->json([
                'section'  => $data['section'] ?? 'For You',
                'products' => $products,
                'data'     => $products, // frontend compat
            ]);
        } catch (\Exception $e) {
            Log::warning("AI recommendations unavailable: " . $e->getMessage());
            return response()->json(['section' => 'For You', 'products' => [], 'data' => []]);
        }
    }

    // GET /api/v1/ai/trending
    public function trending(Request $request)
    {
        try {
            $res      = Http::timeout(10)->get($this->aiUrl('/trending'), ['k' => 8]);
            $data     = $res->json();
            $products = $this->enrichProducts($data['ids'] ?? []);
            return response()->json(['section' => $data['section'] ?? 'Trending Now', 'products' => $products]);
        } catch (\Exception $e) {
            return response()->json(['section' => 'Trending Now', 'products' => []]);
        }
    }

    // GET /api/v1/ai/explore
    public function explore(Request $request)
    {
        try {
            $res      = Http::timeout(10)->get($this->aiUrl('/explore'), ['k' => 8]);
            $data     = $res->json();
            $products = $this->enrichProducts($data['ids'] ?? []);
            return response()->json(['section' => $data['section'] ?? 'Explore More', 'products' => $products]);
        } catch (\Exception $e) {
            // Fallback: random products from DB
            $products = Product::where('status', '1')->inRandomOrder()->limit(8)->get();
            return response()->json(['section' => 'Explore More', 'products' => $products]);
        }
    }

    // GET /api/v1/ai/similar/{productId}
    public function similar(Request $request, int $productId)
    {
        try {
            $res      = Http::timeout(10)->get($this->aiUrl("/similar/{$productId}"), ['k' => 6]);
            $data     = $res->json();
            $products = $this->enrichProducts($data['ids'] ?? []);
            return response()->json(['products' => $products]);
        } catch (\Exception $e) {
            // Fallback: same category products
            $product  = Product::find($productId);
            $products = $product
                ? Product::where('category_id', $product->category_id)->where('id', '!=', $productId)->where('status', '1')->limit(6)->get()
                : collect([]);
            return response()->json(['products' => $products]);
        }
    }

    // GET /api/v1/ai/search?q=laptop
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        try {
            $res      = Http::timeout(10)->get($this->aiUrl('/search'), ['q' => $request->q, 'k' => 10]);
            $data     = $res->json();
            $products = $this->enrichProducts($data['ids'] ?? []);
            return response()->json(['products' => $products, 'query' => $request->q]);
        } catch (\Exception $e) {
            return response()->json(['products' => [], 'query' => $request->q]);
        }
    }

    // GET /api/v1/ai/assistant?query=I need a camera for a wedding
    public function assistant(Request $request)
    {
        $request->validate(['query' => 'required|string|min:3|max:500']);

        try {
            $res      = Http::timeout(20)->get($this->aiUrl('/assistant'), ['query' => $request->query]);
            $data     = $res->json();
            $products = $this->enrichProducts($data['ids'] ?? [], 5);
            return response()->json([
                'answer'   => $data['answer'] ?? '',
                'products' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'answer'   => 'AI assistant is temporarily unavailable.',
                'products' => [],
            ]);
        }
    }

    // Keep these stubs so existing apiResource routes don't 404
    public function store(Request $request)  { return $this->index($request); }
    public function show($id)                { return response()->json([]); }
    public function update(Request $request, $id) { return response()->json([]); }
    public function destroy($id)             { return response()->json([]); }
}
