<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLinkMail;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MagicLinkController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function sendLink(Request $request)
    {
        $domain = config('app.utp_email_domain', 'utp.edu.pe');
        $request->validate([
            'email' => ['required', 'email', "ends_with:{$domain}"],
        ], [
            'email.ends_with' => "Debes usar tu correo institucional @{$domain}.",
        ]);

        $email = $request->email;
        MagicLink::where('email', $email)->where('used', false)->delete();

        $token = Str::random(64);
        MagicLink::create([
            'email'      => $email,
            'token'      => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($email)->send(new MagicLinkMail($token));

        return back()->with('status', 'Enviamos un enlace de acceso a tu correo UTP. Revisa tu bandeja de entrada.');
    }

    public function verifyLink(string $token)
    {
        $link = MagicLink::where('token', $token)->first();

        if (!$link || !$link->isValid()) {
            return redirect()->route('login')
                ->withErrors(['token' => 'El enlace expiró o ya fue usado. Solicita uno nuevo.']);
        }

        $link->update(['used' => true]);

        $user = User::firstOrCreate(
            ['email' => $link->email],
            ['name' => explode('@', $link->email)[0], 'email_verified_at' => now()]
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('locales.externos'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
