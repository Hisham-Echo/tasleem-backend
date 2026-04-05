<?php
// app/Http/Controllers/Api/RentalController.php

namespace App\Http\Controllers\Api;

use App\Models\Rental;
use App\Http\Resources\RentalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RentalController extends BaseController
{
    public function index(Request $request)
    {
        $query = Rental::with(['product', 'renter']);

        if ($request->has('renter_id')) {
            $query->where('renter_id', $request->renter_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $rentals = $query->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $rentals,
            RentalResource::collection($rentals),
            'Rentals retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'renter_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'daily_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Check product availability
        $conflictingRental = Rental::where('product_id', $request->product_id)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            })
            ->exists();

        if ($conflictingRental) {
            return $this->sendError('Product not available for selected dates');
        }

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;

        $rental = Rental::create([
            'product_id' => $request->product_id,
            'renter_id' => $request->renter_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'daily_price' => $request->daily_price,
            'total_days' => $days,
            'total_price' => $request->daily_price * $days,
            'status' => 'pending',
        ]);

        return $this->sendResponse(
            new RentalResource($rental->load(['product', 'renter'])),
            'Rental created successfully',
            201
        );
    }

    public function show($id)
    {
        $rental = Rental::with(['product', 'renter', 'payment'])->find($id);

        if (!$rental) {
            return $this->sendError('Rental not found');
        }

        return $this->sendResponse(
            new RentalResource($rental),
            'Rental retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return $this->sendError('Rental not found');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,confirmed,active,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $rental->update($request->only('status'));

        return $this->sendResponse(
            new RentalResource($rental),
            'Rental updated successfully'
        );
    }

    public function destroy($id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return $this->sendError('Rental not found');
        }

        if ($rental->status !== 'pending') {
            return $this->sendError('Cannot delete rental that is not pending');
        }

        $rental->delete();

        return $this->sendResponse(null, 'Rental deleted successfully');
    }
}