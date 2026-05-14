<?php

namespace App\Models;

use App\Enums\PromoCodeType;
use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property PromoCodeType $type
 * @property int $value
 * @property int $min_order_amount
 * @property int|null $max_discount
 * @property int|null $usage_limit
 * @property int $used_count
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $status
 * @method static Builder<static>|PromoCode active()
 * @method static \Database\Factories\PromoCodeFactory factory($count = null, $state = [])
 * @method static Builder<static>|PromoCode newModelQuery()
 * @method static Builder<static>|PromoCode newQuery()
 * @method static Builder<static>|PromoCode query()
 * @method static Builder<static>|PromoCode valid()
 * @method static Builder<static>|PromoCode whereCode($value)
 * @method static Builder<static>|PromoCode whereCreatedAt($value)
 * @method static Builder<static>|PromoCode whereDescription($value)
 * @method static Builder<static>|PromoCode whereExpiresAt($value)
 * @method static Builder<static>|PromoCode whereId($value)
 * @method static Builder<static>|PromoCode whereIsActive($value)
 * @method static Builder<static>|PromoCode whereMaxDiscount($value)
 * @method static Builder<static>|PromoCode whereMinOrderAmount($value)
 * @method static Builder<static>|PromoCode whereType($value)
 * @method static Builder<static>|PromoCode whereUpdatedAt($value)
 * @method static Builder<static>|PromoCode whereUsageLimit($value)
 * @method static Builder<static>|PromoCode whereUsedCount($value)
 * @method static Builder<static>|PromoCode whereValue($value)
 * @mixin \Eloquent
 */
class PromoCode extends Model
{
    use HasActiveScope, HasFactory;

    protected $fillable = [
        'code', 'description', 'type', 'value', 'min_order_amount',
        'max_discount', 'usage_limit', 'used_count',
        'expires_at', 'is_active',
    ];

    protected $casts = [
        'type' => PromoCodeType::class,
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'integer',
        'min_order_amount' => 'integer',
        'max_discount' => 'integer',
        'used_count' => 'integer',
        'usage_limit' => 'integer',
    ];

    /**
     * Check up: is the promo code still valid?
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check by date
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        // Checking the usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Extend the basic scopeActive with date checking
     */
    public function scopeValid(Builder $query): void
    {
        $query->active()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
            });
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (!$this->is_active) {
                    return 'inactive';
                }

                if ($this->expires_at && $this->expires_at->isPast()) {
                    return 'expired';
                }

                if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
                    return 'depleted';
                }

                return 'active';
            },
        );
    }
}
