<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Google_Client;

class AuthController extends Controller
{
    /**
     * Handle a login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        Log::info('Intentando login con credenciales:', $credentials);

        // Busca al usuario manualmente para depuración
        $user = User::where('email', $credentials['email'])->first();
        if ($user) {
            Log::info('Usuario encontrado:', ['id' => $user->id, 'email' => $user->email]);
            $passwordMatches = Hash::check($credentials['password'], $user->password);
            Log::info('Resultado de la verificación de contraseña:', ['matches' => $passwordMatches]);
        } else {
            Log::warning('Usuario no encontrado para email:', ['email' => $credentials['email']]);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info('Login exitoso para usuario:', ['id' => $user->id, 'email' => $user->email]);
            return response()->json(['token' => $token], 200);
        }

        Log::warning('Login fallido para credenciales:', $credentials);
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        Log::info('Datos recibidos en registro:', $request->all());

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        try {
            Log::info('Iniciando transacción para crear usuario:', $validatedData);
            DB::beginTransaction();
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);
            Log::info('Usuario creado antes de commit:', ['id' => $user->id, 'email' => $user->email]);
            DB::commit();
            Log::info('Transacción confirmada, usuario creado con ID:', ['id' => $user->id, 'password_hash' => $user->password]);
            return response()->json(['message' => 'Usuario registrado exitosamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar usuario:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validatedData' => $validatedData,
            ]);
            return response()->json(['message' => 'Error al registrar usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $user->update($validatedData);
        return response()->json(['message' => 'Perfil actualizado', 'user' => $user], 200);
    }

    /**
     * Handle Google login.
     */
    public function handleGoogleLogin(Request $request)
    {
        Log::info('Iniciando handleGoogleLogin con id_token:', ['id_token' => $request->input('id_token')]);

        $idToken = $request->input('id_token');
        if (!$idToken) {
            Log::warning('No se proporcionó id_token');
            return response()->json(['message' => 'Falta el token de Google'], 400);
        }

        // Verificar el token con Google
        try {
            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($idToken);

            if ($payload) {
                $email = $payload['email'];
                $name = $payload['name'];
                Log::info('Token de Google verificado:', ['email' => $email, 'name' => $name]);

                // Buscar o crear el usuario
                $user = User::firstOrCreate(
                    ['email' => $email],
                    ['name' => $name, 'password' => Hash::make(Str::random(16))]
                );

                // Iniciar sesión con el usuario
                Auth::login($user);

                // Generar token con Sanctum
                $token = $user->createToken('auth_token')->plainTextToken;

                Log::info('Inicio de sesión con Google exitoso:', ['user_id' => $user->id, 'email' => $user->email]);
                return response()->json(['token' => $token], 200);
            } else {
                Log::warning('Token de Google inválido');
                return response()->json(['message' => 'Token inválido'], 401);
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar el token de Google:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Error al verificar el token de Google: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }
}