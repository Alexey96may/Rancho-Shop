<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $ordersQuery = $user->orders();
        $latestOrder = $ordersQuery->latest()->first();

        return Inertia::render('Profile/Edit', [
            'latestOrder' => $latestOrder ? OrderResource::make($latestOrder) : null,
            'stats' => [
                'total_orders' => $ordersQuery->count(),
                'total_spent' => (float) $ordersQuery->where('status', '!=', 'cancelled')->sum('total_price'),
            ],
            'seo' => $this->seo('Мой Профиль', robots: 'noindex, nofollow'),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        unset($validated['current_password']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Данные профиля успешно обновлены!');
    }
}
