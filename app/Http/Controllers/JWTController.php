<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Notifications\RegistroUsarioNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class JWTController extends Controller
{

    public function view()
    {
        
    }

    //Registrar Usuario//
    public function registrer(Request $request)
    {
        //VALIDAR VARIABLES//
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'lastname'=>'required|max:255',
            'job'=>'required|max:255',
            'phone'=>'required|max:255'
        ]);
        
        //CREACIÓN DEL USUARIO//
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'lastname'=>$request->lastname,
            'job' => $request->job,
            'phone'=> $request->phone,
            'status' => 'No verificado',
        ]);
        
        $remember_token = Str::random(60); // Genera una cadena aleatoria para el token
        // Asigna el token al usuario
        $user->remember_token = $remember_token;
        $user->save();

        // Envía el correo electrónico de verificación
        $notificacion = $user;
        $notificacion->notify(new RegistroUsarioNotification($request->name, 
                                                            $request->lastname, 
                                                            $request->email, 
                                                            $request->job, 
                                                            $request->phone, 
                                                            $remember_token));
                                                            
        // Genera el token de autenticación JWT
        $token = JWTAuth::fromUser($user);
        
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    //Login Usuario//
    public function login(LoginRequest $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = request(['email', 'password']);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'invalid credentials'
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'not create token'
            ], 500);
        }

        //Autentificar al usuario para la fecha de inicio de sesión//
        $user = Auth::user();
        $user->last_login_at = now();
        $user->save();

        $formatofecha = $user->last_login_at->format('Y-m-d H:i:s');

        return response()->json([
            'token' => $token,
            'user_id' => $user->id,
            'last_login_at' => $formatofecha,
        ]);
    }

    public function verificar($id)
    {
        $user = User::where('remember_token', $id)->first();
    
        if (!$user) {
            return response()->json(['message' => 'Token no válido'], 400);
        }
    
        // Actualiza el estado del usuario a "Verificado"
        $user->status = 'Verificado';
        $user->save();
    
        return response()->json(['message' => 'Cuenta verificada con exito']);
    }

}