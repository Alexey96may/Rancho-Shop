<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SettingService;
use App\Http\Resources\SettingResource;
use Inertia\Inertia;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index()
    {
        $settingsModels = $this->settingService->allModels();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => SettingResource::collection($settingsModels),
            'values' => $this->settingService->all(),
            'seo' => $this->seo('Панель управления: Настройки', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdate(Request $request)
    {
        // Expect an array of the following type: [['key' => 'site_name', 'value' => 'Новое имя', 'type' => 'string'], ...]
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'present', // present allows you to pass empty strings or null
            'settings.*.type' => 'required|string|in:string,integer,boolean,json',
        ]);

        foreach ($validated['settings'] as $item) {
            $this->settingService->set($item['key'], $item['value'], $item['type']);
        }

        return back()->with('success', 'Настройки успешно обновлены');
    }

    /**
     * Reset the settings cache (if something went wrong)
     */
    public function clearCache()
    {
        \Illuminate\Support\Facades\Cache::forget('settings.all');
        \Illuminate\Support\Facades\Cache::forget('delivery_zones');
        
        return back()->with('info', 'Кеш настроек очищен');
    }
}
