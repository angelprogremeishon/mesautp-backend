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
    private const DOMAIN = 'utp.edu.pe';

    /**
     * Paso 1 del login: el usuario escribe su correo.
     * Decide qué pantalla mostrar:
     *   - status="pin"      → ya está registrado, pedirle el PIN.
     *   - status="register" → no existe / sin registro completo, ofrecer enviar enlace.
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'ends_with:@'.self::DOMAIN],
        ], [
            'email.ends_with' => 'Debes usar tu correo institucional @'.self::DOMAIN.'.',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->registro_completo) {
            return response()->json([
                'status' => 'pin',
                'name'   => $user->name,
            ]);
        }

        return response()->json(['status' => 'register']);
    }

    /**
     * Envía un enlace de registro al correo institucional.
     * El enlace lleva a /registro?token=... en el frontend.
     */
    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'ends_with:@'.self::DOMAIN],
        ], [
            'email.ends_with' => 'Debes usar tu correo institucional @'.self::DOMAIN.'.',
        ]);

        $email = $request->email;

        // Si ya completó registro, no reenviar enlace: debe entrar con PIN.
        $existing = User::where('email', $email)->first();
        if ($existing && $existing->registro_completo) {
            return response()->json([
                'message' => 'Esta cuenta ya está registrada. Ingresa con tu PIN.',
                'status'  => 'pin',
            ], 409);
        }

        MagicLink::where('email', $email)->where('used', false)->delete();

        $token = Str::random(64);
        MagicLink::create([
            'email'      => $email,
            'token'      => $token,
            'expires_at' => now()->addMinutes(30),
        ]);

        Mail::to($email)->send(new MagicLinkMail($token));

        return response()->json(['message' => 'Enlace de registro enviado a tu correo UTP.']);
    }

    /**
     * Valida el token del enlace (al abrir /registro?token=...).
     * Devuelve el correo asociado para pre-llenar el formulario.
     */
    public function verifyToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $link = MagicLink::where('token', $request->token)->first();

        if (!$link || !$link->isValid()) {
            return response()->json(['message' => 'El enlace expiró o ya fue usado.'], 422);
        }

        return response()->json([
            'email' => $link->email,
            'valid' => true,
        ]);
    }

    /**
     * Completa el registro: nombre, apellido, DNI y PIN.
     * Marca el token como usado y deja al usuario logueado.
     */
    public function completeRegistro(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string',
            'name'     => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'dni'      => ['required', 'digits:8', 'unique:users,dni'],
            'pin'      => ['required', 'digits_between:4,6', 'confirmed'],
        ], [
            'dni.digits'         => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique'         => 'Este DNI ya está registrado.',
            'pin.digits_between' => 'El PIN debe tener entre 4 y 6 dígitos.',
            'pin.confirmed'      => 'La confirmación del PIN no coincide.',
        ]);

        $link = MagicLink::where('token', $data['token'])->first();
        if (!$link || !$link->isValid()) {
            return response()->json(['message' => 'El enlace expiró o ya fue usado.'], 422);
        }

        $user = User::firstOrNew(['email' => $link->email]);

        // Si ya estaba registrado, no permitir re-registro por esta vía.
        if ($user->exists && $user->registro_completo) {
            return response()->json(['message' => 'Esta cuenta ya está registrada.'], 409);
        }

        $user->fill([
            'name'              => $data['name'],
            'apellido'          => $data['apellido'],
            'dni'               => $data['dni'],
            'pin'               => $data['pin'],          // se hashea por el cast
            'registro_completo' => true,
            'email_verified_at' => now(),
        ]);
        if (!$user->role) {
            $user->role = 'consumidor';
        }
        $user->save();

        $link->update(['used' => true]);

        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ], 201);
    }

    /**
     * Login recurrente: correo + PIN.
     */
    public function loginPin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'pin'   => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])
            ->where('registro_completo', true)
            ->first();

        if (!$user || !Hash::check($data['pin'], (string) $user->pin)) {
            return response()->json([
                'message' => 'Correo o PIN incorrectos.',
                'errors'  => ['pin' => ['Correo o PIN incorrectos.']],
            ], 422);
        }

        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'apellido' => $user->apellido,
            'email'    => $user->email,
            'role'     => $user->role,
        ];
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
            'tipo.required'      => 'Selecciona el tipo de emprendedor.',
            'name.required'      => 'Ingresa tu nombre.',
            'email.required'     => 'Ingresa tu correo.',
            'email.email'        => 'Ingresa un correo válido.',
            'email.unique'       => 'Este correo ya tiene una cuenta. Si eres estudiante y quieres vender, inicia sesión y activa tu emprendimiento desde tu panel.',
            'email.ends_with'    => 'El emprendedor interno debe usar su correo @utp.edu.pe.',
            'password.required'  => 'Ingresa una contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'nombre.required'    => 'Ingresa el nombre de tu local.',
            'foto.image'         => 'El archivo debe ser una imagen.',
            'foto.max'           => 'La imagen no debe superar 2 MB.',
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
                // Prototipo: aprobado automáticamente (sin panel de admin todavía).
                'estado'  => 'aprobado',
                'activo'  => true,
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
