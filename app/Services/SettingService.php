<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get the value of a setting by key with automatic customization.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Cache the settings for 24 hours so as not to yank the database every time
        return Cache::remember("setting.{$key}", 86400, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return $this->castValue($setting->value, $setting->type, $key);
        });
    }

    /**
     * Dynamic type casting
     */
    private function castValue(?string $value, string $type, string $key = ''): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if ($key === 'farm_coords' && $type === 'string') {
            return $this->parseCoords($value);
        }

        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value, // string
        };
    }

    /**
     * Update or create a setting
     */
    public function set(string $key, mixed $value, string $type = 'string', bool $flushCache = true): void
    {
        $val = ($type === 'json') ? json_encode($value) : (string) $value;

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $val, 'type' => $type]
        );

        Cache::forget("setting.{$key}");

        if ($flushCache) {
            $this->flushCache();
            if ($key === 'delivery_zones') {
                Cache::forget('delivery_zones');
            }
        }
    }

    public function all(): array
    {
        return Cache::remember("settings.all", 86400, function () {
            return Setting::all()
                ->mapWithKeys(fn ($s) => [
                    $s->key => $this->castValue($s->value, $s->type, $s->key)
                ])
                ->toArray();
        });
    }

    public function bulkSet(array $settings): void
    {
        $hasDeliveryZones = false;

        foreach ($settings as $item) {
            if ($item['key'] === 'delivery_zones') {
                $hasDeliveryZones = true;
            }

            $this->set($item['key'], $item['value'], $item['type'], false);
        }

        $this->flushCache();

        if ($hasDeliveryZones) {
            Cache::forget('delivery_zones');
        }
    }

    /**
    * Get model collections for the admin panel (cache optimized)
    */
    public function allModels()
    {
        $rows = Cache::remember("settings.models", 86400, fn () =>
            Setting::all()->toArray()
        );

        return Setting::hydrate($rows);
    }

    /**
    * Select a specific group of settings
    */
    public function group(array $keys): array
    {
        $all = $this->all();

        return collect($keys)
            ->mapWithKeys(fn ($key) => [$key => $all[$key] ?? null])
            ->toArray();
    }

    /**
    * Parse the coordinate string into a readable array
    */
    private function parseCoords(?string $value): ?array
    {
        if (!$value || !str_contains($value, ',')) {
            return null;
        }

        [$lat, $lng] = explode(',', $value);

        return [
            'lat' => (float) trim($lat),
            'lng' => (float) trim($lng),
        ];
    }

    /**
    * Get delivery zones
    */
    public function deliveryZones(): array
    {
        return Cache::remember('delivery_zones', 3600, function () {
            return $this->get('delivery_zones', []);
        });
    }

    /**
    * Centralized reset of the global settings cache
    */
    public function flushCache(): void
    {
        Cache::forget('settings.all');
        Cache::forget('settings.models');
        Cache::forget('delivery_zones');
    }
}
