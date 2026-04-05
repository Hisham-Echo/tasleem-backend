<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Rental;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Show sales report.
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $orders = Order::with(['user', 'product'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalSales = $orders->sum('total_price');
        $totalOrders = $orders->count();

        return view('admin.reports.sales', compact('orders', 'totalSales', 'totalOrders', 'startDate', 'endDate'));
    }

    /**
     * Show rentals report.
     */
    public function rentals(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $rentals = Rental::with(['product', 'renter'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalRentals = $rentals->sum('total_price');
        $rentalCount = $rentals->count();

        return view('admin.reports.rentals', compact('rentals', 'totalRentals', 'rentalCount', 'startDate', 'endDate'));
    }

    /**
     * Show users report.
     */
    public function users()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $newUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        
        $usersByRole = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get();

        return view('admin.reports.users', compact('totalUsers', 'activeUsers', 'newUsers', 'usersByRole'));
    }

    /**
     * Show products report.
     */
    public function products()
    {
        $totalProducts = Product::count();
        $availableProducts = Product::where('status', 'available')->count();
        $topProducts = Product::orderBy('pay_count', 'desc')->take(10)->get();
        $topRated = Product::orderBy('rate', 'desc')->take(10)->get();

        return view('admin.reports.products', compact('totalProducts', 'availableProducts', 'topProducts', 'topRated'));
    }

    /**
     * Show revenue report.
     */
    public function revenue(Request $request)
    {
        $year = $request->get('year', now()->year);

        $monthlyRevenue = DB::table('payments')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount) as total'))
            ->whereYear('created_at', $year)
            ->where('status', 'completed')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        return view('admin.reports.revenue', compact('monthlyRevenue', 'year'));
    }
}