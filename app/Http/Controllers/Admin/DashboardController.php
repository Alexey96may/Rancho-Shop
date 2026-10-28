<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardStatsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(DashboardStatsService $statsService): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $statsService->getStats(),
        ]);
    }
}
