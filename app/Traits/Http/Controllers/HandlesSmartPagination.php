<?php

namespace App\Traits\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

trait HandlesSmartPagination
{
    /**
    * Performs a redirect based on the 'back' query parameter. 
    */
    protected function redirectWithFilters(Request $request, string $defaultRoute, string $message = 'Действие выполнено!'): RedirectResponse
    {
        if ($request->filled('back')) {
            return redirect()
                ->to(route($defaultRoute) . $request->input('back'))
                ->with('success', $message);
        }

        return redirect()
            ->route($defaultRoute)
            ->with('success', $message);
    }
}
