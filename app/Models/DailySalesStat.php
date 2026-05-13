<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
