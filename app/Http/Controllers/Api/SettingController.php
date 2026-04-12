<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Services\SettingService;

class SettingController extends Controller
{
    public function index()
    {
        return SettingResource::collection(
            app(SettingService::class)->allModels()
        );
    }
}