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

            return $this->castValue($setting->value, $setting->type);
        });
    }

    /**
     * Dynamic type casting
     */
    private function castValue(?string $value, string $type): mixed
    {
        if (is_null($value)) {
            return null;
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
    public function set(string $key, mixed $value, string $type = 'string'): void
    {
        $val = ($type === 'json') ? json_encode($value) : (string) $value;

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $val, 'type' => $type]
        );

        Cache::forget("setting.{$key}");
        Cache::forget("settings.all");
    }

    public function all(): array
    {
        return Cache::remember("settings.all", 86400, function () {
            return Setting::all()
                ->mapWithKeys(fn ($s) => [
                    $s->key => $this->castValue($s->value, $s->type)
                ])
                ->toArray();
        });
    }

    public function group(array $keys): array
    {
        $all = $this->all();

        return collect($keys)
            ->mapWithKeys(fn ($key) => [$key => $all[$key] ?? null])
            ->toArray();
    }

    private function parseCoords(?string $value): ?array
    {
        if (!$value) return null;

        [$lat, $lng] = explode(',', $value);

        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng,
        ];
    }
}
