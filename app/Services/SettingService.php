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
        if (is_null($value)) return null;

        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            default   => $value, // string
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
    }
}