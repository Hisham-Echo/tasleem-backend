<?php
namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends BaseController
{
    public function index(Request $request)
    {
        $query = Payment::with(['order', 'rental']);

        if (!auth()->user()?->isAdmin()) {
            $query->where('user_id', auth()->id());
        } elseif ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $payments,
            PaymentResource::collection($payments),
            'Payments retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'       => 'nullable|exists:orders,id',
            'rental_id'      => 'nullable|exists:rentals,id',
            'amount'         => 'required|numeric|min:0',
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer,cash,vodafone_cash,instapay',
            'transaction_id' => 'nullable|string|unique:payments',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $payment = Payment::create([
            'user_id'        => auth()->id(),
            'order_id'       => $request->order_id,
            'rental_id'      => $request->rental_id,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'status'         => 'completed',
        ]);

        return $this->sendResponse(
            new PaymentResource($payment->load(['order', 'rental'])),
            'Payment recorded successfully',
            201
        );
    }

    public function show($id)
    {
        $payment = Payment::with(['order', 'rental'])->find($id);

        if (!$payment) {
            return $this->sendError('Payment not found', [], 404);
        }

        if (!auth()->user()?->isAdmin() && $payment->user_id !== auth()->id()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        return $this->sendResponse(new PaymentResource($payment), 'Payment retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return $this->sendError('Payment not found', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,completed,failed,refunded',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $payment->update($request->only(['status']));
        return $this->sendResponse(new PaymentResource($payment), 'Payment updated successfully');
    }

    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (!$payment) return $this->sendError('Payment not found', [], 404);
        $payment->delete();
        return $this->sendResponse(null, 'Payment deleted successfully');
    }
}
