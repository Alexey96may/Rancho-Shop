<?php

use App\Services\SettingService;

if (!function_exists('setting')) {
    /**
    * Global helper for accessing settings
    *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}