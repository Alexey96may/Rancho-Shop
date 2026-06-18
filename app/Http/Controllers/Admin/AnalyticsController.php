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

        $orderStats = Order::getOverviewStats();
        $usersCount = User::count();

        return Inertia::render('Admin/Analytics/Index', [
            'overview' => array_merge($orderStats, [
                'users_count' => $usersCount,
            ]),
            'filters' => [
                'days' => $days
            ],
            // Lazy Loading
            'charts' => Inertia::defer(function () use ($days) {
                $stats = DailySalesStat::query()
                    ->forDays($days)
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
