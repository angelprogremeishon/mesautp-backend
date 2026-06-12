<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\MagicLinkMail;
use App\Models\Categoria;
use App\Models\Local;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function emprendedorRegister(Request $request)
    {
        $emailRules = ['required', 'email', 'max:255', 'unique:users,email'];
        if ($request->input('tipo') === 'interno') {
            $emailRules[] = 'ends_with:@utp.edu.pe';
        }

        $data = $request->validate([
            'tipo'             => 'required|in:externo,interno',
            'name'             => 'required|string|max:255',
            'email'            => $emailRules,
            'password'         => 'required|string|min:6|confirmed',
            'nombre'           => 'required|string|max:100',
            'descripcion'      => 'nullable|string|max:500',
            'categoria'        => 'nullable|string|max:50',
            'codigo_matricula' => 'nullable|string|max:20',
            'ciclo_carrera'    => 'nullable|string|max:120',
            'direccion'        => 'nullable|string|max:200',
            'punto_entrega'    => 'nullable|string|max:100',
            'horario'          => 'nullable|string|max:100',
            'yape'             => 'nullable|string|max:20',
            'plin'             => 'nullable|string|max:20',
            'whatsapp'         => 'nullable|string|max:20',
            'foto'             => 'nullable|image|max:2048',
        ], [
            'email.ends_with' => 'El emprendedor interno debe usar su correo @utp.edu.pe.',
        ]);

        $user = DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'role'              => 'emprendedor',
                'email_verified_at' => now(),
            ]);

            $localData = collect($data)->only([
                'nombre', 'tipo', 'descripcion', 'codigo_matricula', 'ciclo_carrera',
                'direccion', 'punto_entrega', 'horario', 'yape', 'plin', 'whatsapp',
            ])->toArray();

            if (!empty($data['categoria'])) {
                $localData['categoria_id'] = Categoria::firstOrCreate(['nombre' => $data['categoria']])->id;
            }
            if ($request->hasFile('foto')) {
                $localData['foto'] = $request->file('foto')->store('locales', 'public');
            }

            Local::create([
                ...$localData,
                'user_id' => $user->id,
                'estado'  => 'pendiente',
            ]);

            return $user;
        });

        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 201);
    }

    public function emprendedorLogin(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])
            ->where('role', 'emprendedor')
            ->first();

        if (!$user || !Hash::check($data['password'], (string) $user->password)) {
            return response()->json([
                'message' => 'Correo o contraseña incorrectos.',
                'errors'  => ['email' => ['Correo o contraseña incorrectos.']],
            ], 422);
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
