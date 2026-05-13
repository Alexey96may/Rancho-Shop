<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

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
