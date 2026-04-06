<?php
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
        $query = Rental::with(['product']);

        if (!auth()->user()?->isAdmin()) {
            $query->where('renter_id', auth()->id());
        } elseif ($request->has('renter_id')) {
            $query->where('renter_id', $request->renter_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $rentals = $query->latest()->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $rentals,
            RentalResource::collection($rentals),
            'Rentals retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'  => 'required|exists:products,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'daily_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);
        $days  = max(1, $start->diffInDays($end));

        // Use product's daily price if not provided
        $product    = \App\Models\Product::find($request->product_id);
        $dailyPrice = $request->daily_price ?? $product?->daily_rental_price ?? 0;

        $rental = Rental::create([
            'product_id'  => $request->product_id,
            'renter_id'   => auth()->id(),
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'daily_price' => $dailyPrice,
            'total_days'  => $days,
            'total_price' => $dailyPrice * $days,
            'status'      => 'pending',
        ]);

        return $this->sendResponse(
            new RentalResource($rental->load('product')),
            'Rental created successfully',
            201
        );
    }

    public function show($id)
    {
        $rental = Rental::with(['product'])->find($id);

        if (!$rental) {
            return $this->sendError('Rental not found', [], 404);
        }

        if (!auth()->user()?->isAdmin() && $rental->renter_id !== auth()->id()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        return $this->sendResponse(new RentalResource($rental), 'Rental retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return $this->sendError('Rental not found', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,confirmed,active,completed,cancelled,returned',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $rental->update($request->only('status'));
        return $this->sendResponse(new RentalResource($rental), 'Rental updated successfully');
    }

    public function destroy($id)
    {
        $rental = Rental::where('renter_id', auth()->id())->find($id);

        if (!$rental) {
            return $this->sendError('Rental not found', [], 404);
        }

        $rental->delete();
        return $this->sendResponse(null, 'Rental deleted successfully');
    }
}
