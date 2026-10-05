<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SettingService;
use App\Http\Requests\Admin\BulkUpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

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
    public function bulkUpdate(BulkUpdateSettingsRequest $request)
    {
        $this->settingService->bulkSet(
            $request->validated('settings')
        );

        return back()->with('success', 'Настройки успешно обновлены');
    }

    /**
     * Reset the settings cache (if something went wrong)
     */
    public function clearCache()
    {
        $this->settingService->flushCache();
        
        return back()->with('info', 'Кеш настроек очищен');
    }
}
