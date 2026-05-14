<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property int $total_revenue Revenue in cents
 * @property int $orders_count
 * @property int $items_count Number of sold units
 * @property int $avg_order_value Average Check
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereAvgOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereItemsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereOrdersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereTotalRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DailySalesStat extends Model
{
    protected $fillable = [
        'date',
        'total_revenue',
        'orders_count',
        'items_count',
        'avg_order_value',
    ];

    protected $casts = [
        'date' => 'date',
        'total_revenue' => 'integer',
        'orders_count' => 'integer',
        'items_count' => 'integer',
        'avg_order_value' => 'integer',
    ];
}
