<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\MagicLinkMail;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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

        return response()->json(['message' => 'Enlace enviado a tu correo UTP.']);
    }

    public function verifyToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $link = MagicLink::where('token', $request->token)->first();

        if (!$link || !$link->isValid()) {
            return response()->json(['message' => 'El enlace expiró o ya fue usado.'], 422);
        }

        $link->update(['used' => true]);

        $user = User::firstOrCreate(
            ['email' => $link->email],
            ['name' => explode('@', $link->email)[0], 'email_verified_at' => now()]
        );

        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function me(Request $request)
    {
        $user  = $request->user();
        $local = $user->local;

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
            'local' => $local ? [
                'id'     => $local->id,
                'nombre' => $local->nombre,
                'tipo'   => $local->tipo,
                'estado' => $local->estado,
            ] : null,
        ]);
    }
}
