<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

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
}
