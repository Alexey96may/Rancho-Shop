<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLoginController extends Controller
{
    public function login(Request $request, User $user)
    {
        // Middleware 'signed' уже проверил подпись и срок.
        Auth::login($user);

        return redirect()
            ->route('profile.edit')
            ->with('info', 'Вы вошли по временной ссылке. Рекомендуем установить пароль для постоянного доступа.');
    }
}
