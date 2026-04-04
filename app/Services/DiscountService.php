<?php

namespace App\Services;

use App\Models\PromoCode;
use InvalidArgumentException;

class DiscountService
{
    /**
     * Calculate the total discount amount.
     * All calculations are in cents.
     */
    public function calculatePromoDiscount(PromoCode $promo, int $orderAmount): int
    {
        if (! $promo->isValid()) {
            return 0;
        }

        if ($orderAmount < $promo->min_order_amount) {
            return 0;
        }

        $discount = $this->applyFormula($promo, $orderAmount);

        // If there is a limit on the maximum discount (for percentages)
        if ($promo->max_discount && $discount > $promo->max_discount) {
            $discount = $promo->max_discount;
        }

        // The discount cannot be greater than the order amount
        return min($discount, $orderAmount);
    }

    /**
     * Internal logic for applying the formula
     */
    private function applyFormula(PromoCode $promo, int $orderAmount): int
    {
        return match ($promo->type) {
            'fixed' => $promo->value,
            'percent' => (int) ($orderAmount * ($promo->value / 100)),
            default => throw new InvalidArgumentException("Unknown promo type: {$promo->type}"),
        };
    }
}
