<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\DailySalesStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    /**
    * Update statistics based on an order.
    */
    public function updateStats(Order $order): void
    {
        // date only, without time
        $date = Carbon::parse($order->created_at)->startOfDay();
        $dateString = $date->format('Y-m-d');

        $statusValue = $order->status instanceof \UnitEnum ? $order->status->value : $order->status;

        // Retrieve aggregates for all COMPLETED orders for the current day.
        // This is slightly more "resource-intensive" than a simple increment, but guarantees 100% data accuracy.
        $dayStats = Order::where('status', $statusValue)
            ->whereBetween('created_at', [
                $date->copy()->startOfDay(), 
                $date->copy()->endOfDay()
            ])
            ->select([
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_price) as total_revenue'),
            ])
            ->first();

        if ($dayStats && $dayStats->orders_count > 0) {
            $totalRevenue = (int)$dayStats->total_revenue;
            $count = (int)$dayStats->orders_count;

            DailySalesStat::updateOrCreate(
                ['date' => $dateString],
                [
                    'total_revenue'   => $totalRevenue,
                    'orders_count'    => $count,
                    'avg_order_value' => $count > 0 ? (int)($totalRevenue / $count) : 0,
                ]
            );
            
            Log::info("Analytics updated for {$dateString}: Orders {$count}, Total Revenue {$totalRevenue}");
        } else {
            Log::warning("No data found for statistics for order {$order->id} ({$dateString}).");
        }
    }
}
