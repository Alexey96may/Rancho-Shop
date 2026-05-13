<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\DailySalesStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
    * Update statistics based on an order.
    */
    public function updateStats(Order $order): void
    {
        // date only, without time
        $date = Carbon::parse($order->created_at)->format('Y-m-d');

        // Retrieve aggregates for all COMPLETED orders for the current day.
        // This is slightly more "resource-intensive" than a simple increment, but guarantees 100% data accuracy.
        $dayStats = Order::where('status', 'completed')
            ->whereDate('created_at', $date)
            ->select([
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_price) as total_revenue'),
            ])
            ->first();

        if ($dayStats && $dayStats->orders_count > 0) {
            DailySalesStat::updateOrCreate(
                ['date' => $date],
                [
                    'total_revenue'   => $dayStats->total_revenue,
                    'orders_count'    => $dayStats->orders_count,
                    'avg_order_value' => (int)($dayStats->total_revenue / $dayStats->orders_count),
                    // items_count can be added if a relationship to order_items is established.
                ]
            );
        }
    }
}
