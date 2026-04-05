<?php
// app/Http/Controllers/Api/PaymentController.php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends BaseController
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'order', 'rental']);

        if ($request->has('user_id')) {
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
            'order_id' => 'nullable|exists:orders,order_id',
            'rental_id' => 'nullable|exists:rentals,rental_id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer,cash',
            'transaction_id' => 'nullable|string|unique:payments',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        if (!$request->order_id && !$request->rental_id) {
            return $this->sendError('Either order_id or rental_id is required');
        }

        $payment = Payment::create($request->all());

        return $this->sendResponse(
            new PaymentResource($payment->load(['user', 'order', 'rental'])),
            'Payment created successfully',
            201
        );
    }

    public function show($id)
    {
        $payment = Payment::with(['user', 'order', 'rental'])->find($id);

        if (!$payment) {
            return $this->sendError('Payment not found');
        }

        return $this->sendResponse(
            new PaymentResource($payment),
            'Payment retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return $this->sendError('Payment not found');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string|unique:payments,transaction_id,' . $id . ',payment_id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $payment->update($request->only(['status', 'transaction_id']));

        return $this->sendResponse(
            new PaymentResource($payment),
            'Payment updated successfully'
        );
    }

    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return $this->sendError('Payment not found');
        }

        $payment->delete();

        return $this->sendResponse(null, 'Payment deleted successfully');
    }
}