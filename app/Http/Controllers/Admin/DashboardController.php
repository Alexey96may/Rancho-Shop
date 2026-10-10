<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $weekAgo = now()->subWeek();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => [
                    'total' => User::count(),
                    'new' => User::where('created_at', '>=', $weekAgo)->count(),
                ],
                'orders' => [
                    'total' => Order::count(),
                    'new' => Order::where('status', OrderStatus::NEW)->count(),
                    'week' => Order::where('created_at', '>=', $weekAgo)->count(),
                    'revenue' => (int) Order::where('payment_status', 'paid')->sum('total_price'),
                ],
                'comments' => [
                    'total' => Comment::count(),
                    'pending' => Comment::where('status', CommentStatus::PENDING)->count(),
                    'week' => Comment::where('created_at', '>=', $weekAgo)->count(),
                ],
                'products' => [
                    'total' => Product::count(),
                    'active' => Product::active()->count(),
                ],
                'animals' => [
                    'total' => Animal::count(),
                    'active' => Animal::active()->count(),
                ],
                'variants' => [
                    'total' => ProductVariant::count(),
                    'in_stock' => ProductVariant::where('stock', '>', 0)->count(),
                    'out_of_stock' => ProductVariant::where('stock', '<=', 0)->count(),
                    'low_stock' => ProductVariant::query()
                        ->join('units', 'units.id', '=', 'product_variants.unit_id')
                        ->where('product_variants.stock', '>', 0)
                        ->whereColumn('product_variants.stock', '<', 'units.low_stock_threshold')
                        ->count(),
                ],
            ],
        ]);
    }
}
