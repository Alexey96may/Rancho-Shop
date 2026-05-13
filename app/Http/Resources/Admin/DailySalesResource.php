<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailySalesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Format the date for the X-axis: "13 May"
            'label' => $this->date->translatedFormat('d M'),
            // Revenue in rubles for the chart
            'revenue' => round($this->total_revenue / 100),
            'orders' => $this->orders_count,
            'avg_check' => round($this->avg_order_value / 100),
        ];
    }
}
