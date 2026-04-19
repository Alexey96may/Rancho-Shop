<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $ordersQuery = $user->orders();
        $latestOrder = $ordersQuery->latest()->first();

        return Inertia::render('User/Dashboard', [
            'latestOrder' => $latestOrder ? OrderResource::make($latestOrder) : null,
            'stats' => [
                'total_orders' => $ordersQuery->count(),
                'total_spent' => (float) $ordersQuery->where('status', '!=', 'cancelled')->sum('total_price'),
            ],
        ]);
    }
}