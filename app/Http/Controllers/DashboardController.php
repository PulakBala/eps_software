<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Sales
        $totalSales = Sale::sum('total_amount');

        // Pending Deliveries (where delivery_status is not 'delivered')
        $pendingDeliveries = Sale::where('delivery_status', '!=', 'delivered')->count();

        // Due Payments (where payment_status is 'due' or 'partial')
        $duePayments = Sale::whereIn('payment_status', ['due', 'partial'])
            ->sum(DB::raw('total_amount - paid_amount'));

        // Total Customers (unique customer_id count)
        $totalCustomers = Sale::distinct('customer_id')->count('customer_id');

        // Recent Sales (last 5 sales)
        $recentSales = Sale::with('customer')
            ->latest()
            ->take(5)
            ->get();

        // Upcoming Deliveries (next 5 deliveries)
        $upcomingDeliveries = Sale::with('customer')
            ->where('delivery_date', '>', now())
            ->where('delivery_status', '!=', 'delivered')
            ->orderBy('delivery_date')
            ->take(5)
            ->get();

        // Sales data for chart (last 7 days)
        $salesData = Sale::select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('sale_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $salesData->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('M d');
        });

        $chartData = $salesData->pluck('total');

        return view('dashboard', compact(
            'totalSales',
            'pendingDeliveries',
            'duePayments',
            'totalCustomers',
            'recentSales',
            'upcomingDeliveries',
            'chartLabels',
            'chartData'
        ));
    }
} 