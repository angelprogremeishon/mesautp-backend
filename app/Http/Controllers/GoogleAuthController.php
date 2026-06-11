<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect($this->frontendUrl('/auth/callback?error=google_failed'));
        }

        $domain = config('app.utp_email_domain', 'utp.edu.pe');

        if (!str_ends_with($googleUser->getEmail(), "@{$domain}")) {
            return redirect($this->frontendUrl('/auth/callback?error=invalid_domain'));
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'              => $googleUser->getName(),
                'email_verified_at' => now(),
            ]
        );

        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        $token = $user->createToken('google-oauth')->plainTextToken;

        return redirect($this->frontendUrl('/auth/callback?token=' . $token . '&from=google'));
    }

    private function frontendUrl(string $path): string
    {
        return rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . $path;
    }
}
