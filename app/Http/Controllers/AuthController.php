<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Registro de nuevo usuario
    public function register(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Generamos un token JWT para el nuevo usuario
        try {
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'token' => $token,
                'user' => $user
            ], 201);
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo crear el token'], 500);
        }
    }

    // Autenticar usuario
    public function login(Request $request) {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Intentar hacer login con los datos proporcionados
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales incorrectas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo crear el token'], 500);
        }

        // Si el login es exitoso, devolver el token y el usuario
        $user = User::where('email', $request->email)->first();
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'user' => $user
        ]);
    }

    // Actualizar datos del usuario | Se puede enviar el usuario / contraseña o directamente el JWT de autenticación
    public function updateProfile(Request $request) {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Validación personalizada usando Validator
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Actualizar los datos del usuario
        $dataToUpdate = $request->only('name', 'email', 'password');

        // Si se proporcionó una nueva contraseña, la encriptamos y la añadimos a los datos a actualizar
        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        // Actualiza el perfil usando el modelo
        $user->update($dataToUpdate);

        return response()->json(['message' => 'Perfil actualizado correctamente', 'user' => $user], 200);
    }
}
