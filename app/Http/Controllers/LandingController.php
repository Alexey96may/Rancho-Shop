<?php

namespace App\Http\Controllers;

use App\Services\LandingPageService;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index(LandingPageService $service)
    {
        return Inertia::render('HomeView', array_merge($service->getData(), [
            'seo' => $this->seo('Семейное Ранчо', 'Магазин - семейное Ранчо'),
        ]));
    }
}
