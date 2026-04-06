<?php
namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    public function index(Request $request)
    {
        $query = Order::with(['product']);

        // Admins can see all orders; regular users see only theirs
        if (!auth()->user()?->isAdmin()) {
            $query->where('user_id', auth()->id());
        } elseif ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $orders,
            OrderResource::collection($orders),
            'Orders retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $order = Order::create([
            'user_id'     => auth()->id(),
            'product_id'  => $request->product_id,
            'quantity'    => $request->quantity,
            'unit_price'  => $request->unit_price,
            'total_price' => $request->quantity * $request->unit_price,
            'status'      => 'pending',
        ]);

        $order->product?->increment('pay_count', $request->quantity);
        $order->product?->decrement('quantity', $request->quantity);

        return $this->sendResponse(
            new OrderResource($order->load('product')),
            'Order created successfully',
            201
        );
    }

    public function show($id)
    {
        $order = Order::with(['product'])->find($id);

        if (!$order) {
            return $this->sendError('Order not found', [], 404);
        }

        // Users can only view their own orders
        if (!auth()->user()?->isAdmin() && $order->user_id !== auth()->id()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        return $this->sendResponse(new OrderResource($order), 'Order retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->sendError('Order not found', [], 404);
        }

        if (!auth()->user()?->isAdmin() && $order->user_id !== auth()->id()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'status'     => 'sometimes|in:pending,confirmed,shipped,delivered,cancelled,returned',
            'quantity'   => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $order->update($request->only(['status', 'quantity', 'unit_price']));

        if ($request->has('quantity') || $request->has('unit_price')) {
            $order->total_price = $order->quantity * $order->unit_price;
            $order->save();
        }

        return $this->sendResponse(new OrderResource($order), 'Order updated successfully');
    }

    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->sendError('Order not found', [], 404);
        }

        if (!auth()->user()?->isAdmin() && $order->user_id !== auth()->id()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        if ($order->status !== 'pending') {
            return $this->sendError('Cannot delete an order that is not pending');
        }

        $order->delete();
        return $this->sendResponse(null, 'Order deleted successfully');
    }
}
