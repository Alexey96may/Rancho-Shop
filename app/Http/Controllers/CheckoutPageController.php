<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\CheckoutService;
use Inertia\Inertia;

class CheckoutPageController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    public function __invoke()
    {
        return Inertia::render('Checkout');
    }
}
