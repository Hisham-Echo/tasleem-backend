<?php
// app/Http/Controllers/Admin/RentalController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    /**
     * Display a listing of the rentals.
     */
    public function index()
    {
        $rentals = Rental::with(['product', 'renter'])->latest()->paginate(15);
        return view('admin.rentals.index', compact('rentals'));
    }

    /**
     * Display the specified rental.
     */
    public function show(Rental $rental)
    {
        $rental->load(['product', 'renter', 'payment']);
        return view('admin.rentals.show', compact('rental'));
    }

    /**
     * Update the rental status.
     */
    public function updateStatus(Request $request, Rental $rental)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,active,completed,cancelled',
        ]);

        $rental->update($validated);

        return redirect()->route('admin.rentals.index')
            ->with('success', 'Rental status updated successfully.');
    }
}