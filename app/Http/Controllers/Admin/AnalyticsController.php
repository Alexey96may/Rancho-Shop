<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DailySalesResource;
use App\Models\DailySalesStat;
use App\Models\Order;
use App\Models\User;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Analytics/Index', [
            'overview' => [
                'total_revenue' => Order::where('status', 'completed')->sum('total_price'),
                'orders_count'  => Order::count(),
                'users_count'   => User::count(),
            ],

            // Lazy Loading
            'charts' => Inertia::defer(function () {
                $stats = DailySalesStat::orderBy('date', 'asc')
                    ->limit(30)
                    ->get();

                return [
                    'sales' => DailySalesResource::collection($stats),
                ];
            })
        ]);
    }
}
