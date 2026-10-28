<?php

namespace App\Services\Admin;

use App\Enums\CommentStatus;
use App\Enums\OrderStatus;
use App\Models\Animal;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\User;

class DashboardStatsService
{
    public function getStats(): array
    {
        $weekAgo = now()->subWeek();

        return [
            'users' => $this->getUserStats($weekAgo),
            'orders' => $this->getOrderStats($weekAgo),
            'comments' => $this->getCommentStats($weekAgo),
            'products' => $this->getProductStats(),
            'animals' => $this->getAnimalStats(),
            'variants' => $this->getVariantStats(),
            'promocodes' => $this->getPromoCodeStats(),
        ];
    }

    private function getUserStats($weekAgo): array
    {
        return [
            'total' => User::count(),
            'new' => User::where('created_at', '>=', $weekAgo)->count(),
        ];
    }

    private function getOrderStats($weekAgo): array
    {
        return [
            'total' => Order::count(),
            'new' => Order::where('status', OrderStatus::NEW)->count(),
            'week' => Order::where('created_at', '>=', $weekAgo)->count(),
            'revenue' => (int) Order::where('payment_status', 'paid')->sum('total_price'),
        ];
    }

    private function getCommentStats($weekAgo): array
    {
        return [
            'total' => Comment::count(),
            'pending' => Comment::where('status', CommentStatus::PENDING)->count(),
            'week' => Comment::where('created_at', '>=', $weekAgo)->count(),
        ];
    }

    private function getProductStats(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::active()->count(),
        ];
    }

    private function getAnimalStats(): array
    {
        return [
            'total' => Animal::count(),
            'active' => Animal::active()->count(),
        ];
    }

    private function getVariantStats(): array
    {
        return [
            'total' => ProductVariant::count(),
            'in_stock' => ProductVariant::where('stock', '>', 0)->count(),
            'out_of_stock' => ProductVariant::where('stock', '<=', 0)->count(),
            'low_stock' => ProductVariant::query()
                ->join('units', 'units.id', '=', 'product_variants.unit_id')
                ->where('product_variants.stock', '>', 0)
                ->whereColumn('product_variants.stock', '<', 'units.low_stock_threshold')
                ->count(),
        ];
    }

    private function getPromoCodeStats(): array
    {
        return [
            'total' => PromoCode::count(),
            'active' => PromoCode::active()->count(),
            'used' => PromoCode::where('used_count', '>', 0)->count(),
        ];
    }
}
