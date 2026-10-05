<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int|null $promo_code_id
 * @property string $customer_name
 * @property string $customer_phone
 * @property string|null $customer_comment
 * @property bool $is_pickup
 * @property string|null $delivery_address
 * @property numeric|null $delivery_lat
 * @property numeric|null $delivery_lng
 * @property bool $delivery_validated
 * @property array<array-key, mixed>|null $delivery_meta
 * @property int $discount_total
 * @property int $total_price
 * @property int $delivery_price
 * @property OrderStatus $status
 * @property string|null $admin_note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PromoCode|null $promoCode
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereAdminNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIsPickup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePromoCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @method static Builder<static>|Order filter(array $filters)
 * @mixin \Eloquent
 */
#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable = [
        'user_id', 'promo_code_id', 'customer_name', 'customer_phone', 'delivery_address',
        'delivery_lat', 'delivery_lng', 'is_pickup', 'delivery_validated',
        'delivery_meta', 'customer_comment', 'discount_total',
        'total_price', 'delivery_price', 'status', 'admin_note',
    ];

    protected $casts = [
        'delivery_meta' => 'array',
        'status' => OrderStatus::class,
    ];

    /**
     * Relation with an user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation with order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relation with promo code
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
    * Scope for filtering orders by customer, ID, and status
    */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($query, $search) {
                $search = mb_strtolower($search, 'UTF-8');
                
                $query->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(customer_name) LIKE ?', ["%{$search}%"])
                      ->orWhere('id', 'LIKE', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            });
    }

    /**
    * Order and revenue statistics in ONE query
    */
    public static function getStats(): array
    {
        $totals = self::selectRaw("
            COUNT(*) as total_count,
            SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) as completed_revenue,
            SUM(CASE WHEN status IN ('confirmed', 'delivering') THEN total_price ELSE 0 END) as pending_revenue
        ")->first();

        return [
            'total_count'             => (int) $totals->total_count,
            'total_completed_revenue' => (int) $totals->completed_revenue,
            'total_pending_revenue'   => (int) $totals->pending_revenue,
        ];
    }

    /**
    * Get the total revenue for completed orders and their total quantity per request
    */
    public static function getOverviewStats(): array
    {
        $stats = self::selectRaw("
            SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) as total_revenue,
            COUNT(*) as orders_count
        ")->first();

        return [
            'total_revenue' => (int) ($stats->total_revenue ?? 0),
            'orders_count'  => (int) $stats->orders_count,
        ];
    }
}
