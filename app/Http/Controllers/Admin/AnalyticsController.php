<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DailySalesResource;
use Illuminate\Http\Request;
use App\Models\DailySalesStat;
use App\Models\Order;
use App\Models\User;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $days = $request->integer('days', 30);

        if (!in_array($days, [7, 30, 90, 0])) {
            $days = 30;
        }

        return Inertia::render('Admin/Analytics/Index', [
            'overview' => [
                'total_revenue' => Order::where('status', 'completed')->sum('total_price'),
                'orders_count'  => Order::count(),
                'users_count'   => User::count(),
            ],

            'filters' => [
                'days' => $days
            ],

            // Lazy Loading
            'charts' => Inertia::defer(function () use ($days) {
                $stats = DailySalesStat::orderBy('date', 'desc')
                    ->when($days > 0, function ($query) use ($days) {
                        return $query->limit($days);
                    })
                    ->get()
                    ->reverse()
                    ->values();

                return [
                    'sales' => DailySalesResource::collection($stats),
                ];
            })
        ]);
    }
}
