<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    // Redirect to Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Processing the response from Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Authentication failed');
        }

        // Account "Merge" Logic:
        // Search for a user by Google ID OR by email
        $user = User::where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if ($user) {
            // If found by email, but Google_ID is not yet linked, link it
            $user->update([
                'google_id' => $googleUser->id,
                'avatar_url' => $user->avatar_url ?? $googleUser->getAvatar(),
            ]);
        } else {
            // If we didn't find anything at all, we register a new one.
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar_url' => $googleUser->getAvatar(),
                'password' => null, 
            ]);
        }

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
