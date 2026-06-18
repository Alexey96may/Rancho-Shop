<?php

namespace App\Actions\Checkout;

use App\DTO\DeliveryDTO;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ValidateDeliveryAction
{
    public function __construct(
        protected SettingService $settings
    ) {}

    public function handle(DeliveryDTO $delivery): array
    {
        if ($delivery->is_pickup) {
            return [
                'is_valid' => true,
                'delivery_price' => 0,
                'zone' => null,
                'distance' => 0,
            ];
        }

        $lat = round($delivery->lat, 7);
        $lng = round($delivery->lng, 7);

        $cacheKey = "delivery_check:{$lat}:{$lng}";

        return Cache::remember($cacheKey, 300, function () use ($delivery) {

            $zones = $this->settings->deliveryZones();

            $farmCoords = $this->settings->get('farm_coords'); // "44.8621, 34.2154"
            [$farmLat, $farmLng] = array_map('floatval', explode(',', $farmCoords));

            foreach ($zones as $zone) {

                if (!$zone['enabled']) continue;

                $distanceToRoute = $this->checkPointInPolylineZoneDistance(
                    $delivery->lat,
                    $delivery->lng,
                    $zone['path']
                );

                if ($distanceToRoute <= ($zone['radius'] * 2)) {

                    $deliveryPrice = $zone['delivery_price'];

                    $distanceToFarm = $this->haversineDistance(
                        $delivery->lat,
                        $delivery->lng,
                        $farmLat,
                        $farmLng
                    );

                    return [
                        'is_valid' => true,
                        'delivery_price' => $deliveryPrice,
                        'zone' => $zone,

                        // 🔥 ВОТ ОНО
                        'distance_to_route' => $distanceToRoute,
                        'distance_to_farm' => $distanceToFarm,
                    ];
                }
            }

            throw ValidationException::withMessages([
                'delivery' => 'Адрес вне зоны доставки' . $farmCoords,
            ]);
        });
    }

    /**
     * Возвращает минимальную дистанцию до маршрута
     */
    private function checkPointInPolylineZoneDistance(
        float $lat,
        float $lng,
        array $path
    ): float {
        $minDistance = INF;

        foreach ($path as $i => $point) {
            if (!isset($path[$i + 1])) continue;

            [$lat1, $lng1] = $point;
            [$lat2, $lng2] = $path[$i + 1];

            $distance = $this->distancePointToSegment(
                $lng,
                $lat,
                $lat1,
                $lng1,
                $lat2,
                $lng2
            );

            $minDistance = min($minDistance, $distance);
        }

        return $minDistance;
    }

    /**
     * Реальное расстояние до фермы
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * расстояние до отрезка (как у тебя было)
     */
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