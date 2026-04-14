<?php

namespace App\Actions\Checkout;

use App\DTO\DeliveryDTO;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;

class ValidateDeliveryAction
{
    public function __construct(
        protected SettingService $settings
    ) {}

    public function handle(DeliveryDTO $delivery): array
    {
        // Pickup is always valid and has no delivery fee
        if ($delivery->is_pickup) {
            return [
                'is_valid' => true,
                'delivery_price' => 0,
                'zone' => null,
            ];
        }

        // Cache key depends on rounded coordinates + current zones snapshot
        $lat = round((float) $delivery->lat, 5);
        $lng = round((float) $delivery->lng, 5);

        $zones = $this->settings->deliveryZones();

        $zonesSignature = md5(json_encode($zones, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $cacheKey = "delivery_check:{$zonesSignature}:{$lat}:{$lng}";

        return Cache::remember($cacheKey, 600, function () use ($delivery, $zones) {
            foreach ($zones as $zone) {
                if (empty($zone['enabled'])) {
                    continue;
                }

                $inside = $this->checkPointInPolylineZone(
                    (float) $delivery->lat,
                    (float) $delivery->lng,
                    $zone['path'] ?? [],
                    (float) ($zone['radius'] ?? 0)
                );

                if ($inside) {
                    return [
                        'is_valid' => true,
                        'delivery_price' => (int) ($zone['delivery_price'] ?? 0),
                        'zone' => $zone,
                    ];
                }
            }

            return [
                'is_valid' => false,
                'delivery_price' => 0,
                'zone' => null,
            ];
        });
    }

    private function checkPointInPolylineZone(
        float $lat,
        float $lng,
        array $path,
        float $radiusMeters
    ): bool {
        if (count($path) < 2) {
            return false;
        }

        foreach ($path as $i => $point) {
            if (!isset($path[$i + 1])) {
                continue;
            }

            [$lat1, $lng1] = $point;
            [$lat2, $lng2] = $path[$i + 1];

            $distance = $this->distancePointToSegment(
                $lat,
                $lng,
                (float) $lat1,
                (float) $lng1,
                (float) $lat2,
                (float) $lng2
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

        $toRad = fn (float $deg): float => $deg * pi() / 180;

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

        $param = $lenSq !== 0.0 ? $dot / $lenSq : -1.0;

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