<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    /**
    * Return the frontend URL where the user should be redirected
    */
    public function getGoogleUrl(): JsonResponse
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    /**
    * Processing data from Google and issuing an API token
    */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            // .stateless() is required for the API since there are no sessions (cookies)
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'google_id' => $googleUser->getId(),
                    'name' => $googleUser->getName(),
                    'avatar_url' => $googleUser->getAvatar(),
                    // password remains null
                ]
            );

            // Create a token (if you use Sanctum)
            $token = $user->createToken('google-auth')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid credentials or Google error'], 422);
        }
    }
}