<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailAuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\AiRecommendationController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ProductImageController;

// ── Public ─────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('forgot-password', [EmailAuthController::class, 'forgotPassword']);
    Route::post('reset-password',  [EmailAuthController::class, 'resetPassword']);
    Route::get('verify-email/{id}/{hash}', [EmailAuthController::class, 'verifyEmail'])->name('verification.verify');

    Route::get('products',              [ProductController::class, 'index']);
    Route::get('products/{id}',         [ProductController::class, 'show']);
    Route::get('products/{id}/similar', [ProductController::class, 'similar']);
    Route::get('categories',      [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('reviews',      [ReviewController::class, 'index']);
    Route::get('reviews/{id}', [ReviewController::class, 'show']);
    Route::get('products/{productId}/images', [ProductImageController::class, 'index']);
    Route::get('product-images/{id}',         [ProductImageController::class, 'show']);

    // Public AI endpoints (trending & explore — no auth needed)
    Route::get('ai/trending', [AiRecommendationController::class, 'trending']);
    Route::get('ai/explore',  [AiRecommendationController::class, 'explore']);
});

// ── Protected ───────────────────────────────────────────────────────
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me',      [AuthController::class, 'me']);
    Route::post('email/verification-notification', [EmailAuthController::class, 'resendVerification']);

    // Users
    Route::apiResource('users', UserController::class);
    Route::get('users/{id}/products', [UserController::class, 'products']);
    Route::get('users/{id}/orders',   [UserController::class, 'orders']);
    Route::get('users/{id}/rentals',  [UserController::class, 'rentals']);

    // Products / Categories / Orders / Rentals / Reviews / Payments
    Route::apiResource('products',   ProductController::class)->except(['index','show']);
    Route::apiResource('categories', CategoryController::class)->except(['index','show']);
    Route::apiResource('orders',   OrderController::class);
    Route::apiResource('rentals',  RentalController::class);
    Route::apiResource('reviews',  ReviewController::class)->except(['index','show']);
    Route::apiResource('payments', PaymentController::class);

    // Cart
    Route::delete('cart/clear/{user_id}', [CartItemController::class, 'clear']);
    Route::apiResource('cart', CartItemController::class);

    // Wishlist
    Route::get('wishlist',                   [WishlistController::class, 'index']);
    Route::post('wishlist',                  [WishlistController::class, 'store']);
    Route::get('wishlist/check',             [WishlistController::class, 'check']);
    Route::delete('wishlist/clear/{userId}', [WishlistController::class, 'clear']);
    Route::delete('wishlist/{id}',           [WishlistController::class, 'destroy']);

    // AI — personalized & interactive (auth required)
    Route::get('recommendations',       [AiRecommendationController::class, 'index']);
    Route::get('ai/similar/{productId}',[AiRecommendationController::class, 'similar']);
    Route::get('ai/search',             [AiRecommendationController::class, 'search']);
    Route::get('ai/assistant',          [AiRecommendationController::class, 'assistant']);

    // Logs (admin only)
    Route::middleware('admin')->prefix('logs')->group(function () {
        Route::get('/stats',                          [LogController::class, 'stats']);
        Route::get('/entity/{entityType}/{entityId}', [LogController::class, 'entityLogs']);
        Route::get('/user/{userId}',                  [LogController::class, 'userLogs']);
        Route::get('/',                               [LogController::class, 'index']);
        Route::get('/{id}',                           [LogController::class, 'show']);
    });

    // Product images
    Route::post('product-images/upload',            [ProductImageController::class, 'store']);
    Route::post('product-images/upload-single',     [ProductImageController::class, 'uploadSingle']);
    Route::delete('product-images/delete-multiple', [ProductImageController::class, 'destroyMultiple']);
    Route::put('product-images/{id}',               [ProductImageController::class, 'update']);
    Route::delete('product-images/{id}',            [ProductImageController::class, 'destroy']);
});
