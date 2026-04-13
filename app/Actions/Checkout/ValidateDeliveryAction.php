<?php

namespace App\Actions\Checkout;

use App\DTO\DeliveryDTO;
use App\Services\SettingService;
use Illuminate\Validation\ValidationException;

class ValidateDeliveryAction
{
    public function __construct(
        protected SettingService $settings
    ) {}

    public function handle(DeliveryDTO $delivery): array
    {
        // 🟢 Самовывоз
        if ($delivery->is_pickup) {
            return [
                'is_valid' => true,
                'delivery_price' => 0,
                'zone' => null,
            ];
        }

        $zones = $this->settings->deliveryZones();

        foreach ($zones as $zone) {

            if (!$zone['enabled']) continue;

            $inside = $this->checkPointInPolylineZone(
                $delivery->lat,
                $delivery->lng,
                $zone['path'],
                $zone['radius']
            );

            if ($inside) {
                $deliveryPrice = $zone['delivery_price'];

                return [
                    'is_valid' => true,
                    'delivery_price' => $deliveryPrice,
                    'zone' => $zone,
                ];
            }
        }

        throw ValidationException::withMessages([
            'delivery' => 'Адрес вне зоны доставки',
        ]);
    }

    private function checkPointInPolylineZone(
        float $lat,
        float $lng,
        array $path,
        float $radiusMeters
    ): bool {
        foreach ($path as $i => $point) {
            if (!isset($path[$i + 1])) continue;

            [$lat1, $lng1] = $point;
            [$lat2, $lng2] = $path[$i + 1];

            $distance = $this->distancePointToSegment(
                $lat,
                $lng,
                $lat1,
                $lng1,
                $lat2,
                $lng2
            );

            if ($distance <= $radiusMeters) {
                return true;
            }
        }

        return false;
    }

    private function distancePointToSegment(
        float $px,
        float $py,
        float $x1,
        float $y1,
        float $x2,
        float $y2
    ): float {
        $earthRadius = 6371000;

        $toRad = fn($deg) => $deg * pi() / 180;

        $px = $toRad($px);
        $py = $toRad($py);
        $x1 = $toRad($x1);
        $y1 = $toRad($y1);
        $x2 = $toRad($x2);
        $y2 = $toRad($y2);

        $A = $px - $x1;
        $B = $py - $y1;
        $C = $x2 - $x1;
        $D = $y2 - $y1;

        $dot = $A * $C + $B * $D;
        $lenSq = $C * $C + $D * $D;

        $param = $lenSq !== 0 ? $dot / $lenSq : -1;

        if ($param < 0) {
            $xx = $x1;
            $yy = $y1;
        } elseif ($param > 1) {
            $xx = $x2;
            $yy = $y2;
        } else {
            $xx = $x1 + $param * $C;
            $yy = $y1 + $param * $D;
        }

        $dx = $px - $xx;
        $dy = $py - $yy;

        return sqrt($dx * $dx + $dy * $dy) * $earthRadius;
    }
}