<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'promo_code_id', 'customer_name', 'customer_phone', 
        'delivery_address', 'customer_comment', 'discount_total', 
        'total_price', 'delivery_price', 'status', 'admin_note'
    ];

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
