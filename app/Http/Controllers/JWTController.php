<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Notifications\RecuperarPasswordNotification;
use App\Notifications\RegistroUsarioNotification;
use App\Notifications\RestablecerPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JWTController extends Controller
{
    ///////////////////////////////////////FUNCIÓN REGISTRAR NUEVO USUARIO////////////////////////////////////////////
    public function registrer(Request $request)
    {
        try{
            //VALIDAR VARIABLES//
            $this->validate($request, [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                //en password regex evalua que tenga una minuscula, una mayuscula, un numero y un caracter//
                'password' => [
                    'required',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])/',
                ],
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
        
        }catch(ValidationException $e) {
            // Manejar errores de validación
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
    ///////////////////////////////////////////////////LOGIN DE INICIO//////////////////////////////////////////////////////
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

    /////////////////////////////////////FUNCIÓN VERIFICAR STATUS//////////////////////////////////////////////////////////////
    public function verificar($token)
    {
        $user = User::where('remember_token', $token)->first();
    
        if (!$user) {
            return response()->json(['message' => 'Token no válido'], 400);
        }
    
        // Actualiza el estado del usuario a "Verificado"
        $user->status = 'Verificado';
        $user->save();
    
        return response()->json(['message' => 'Cuenta verificada con exito']);
    }

    //////////////////////////////////////////////FUNCIÓN MODIFICAR PASSWORD BOTÓN//////////////////////////////////////////////
    public function RestablecerPassword($id)
    {
        //Busqueda del usuario por su id//
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
            
        } else {
            $name = $user->name;
            $lastname = $user->lastname;
            $user->notify(new RestablecerPassword($name, $lastname));
            //$email = $user->email;
            //return response()->json(['email' => $email], 200);
            return response()->json(['message' => 'Comenzarás el proceso de modificación de tu contraseña'], 200);
        }
    }

    ///////////////////////////////////////////////////FUNCIÓN CONTRASEÑA TEMPORAL/////////////////////////////////////////////
    public function RecuperarPassword($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);

        } else {
            //CREA UNA CONTRASEÑA//
            $newPassword = Str::random(8); 
            //ENCRIPTA LA CONTRASEÑA
            $user->password = Hash::make($newPassword);
            $user->save();
            $name = $user->name;
            $lastname = $user->lastname;
            $email = $user->email;
    
            // Envía la nueva contraseña al usuario por correo electrónico
            $user->notify(new RecuperarPasswordNotification($name, $lastname, $email, $newPassword));
            
            return response()->json(['message' => 'Se envió una nueva contraseña a tu correo'], 201);
        }
    }

    ///////////////////////////////////////////////////////MODIFICAR PASSWORD////////////////////////////////////////////////
    public function ModificarPassword(Request $request)
    {
        try{
            $this->validate($request, [
                'id' => 'required|exists:users,id',
                //en password regex evalua que tenga una minuscula, una mayuscula, un numero y un caracter//
                'password' => [
                    'required',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])/',
                ],
            ]);

            $user = DB::table('users')->where('id', $request->id)
                                    ->where('email', $request->email)->update([
                                    'password'=> Hash::make($request->password)]);

            if($user){
                return response()->json(['message' => 'Se modificó tu contraseña con éxito', $user], 201);
            } else {
                return response()->json(['message' => 'No se pudo modificar la contraseña. Verifica los datos proporcionados.'], 422);
            }
        }catch(ValidationException $e) {
            // Manejar errores de validación
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    ///////////////////////////////////////////FUNCIOÓN ELIMINAR REGISTRO///////////////////////////////////////////////////////
    public function destroyUsers(Request $request)
    {
        DB::table('users')->where('id', $request->id)->delete();
        return response()->json(['message'=>'Usuario eliminado.'], 200);  
    }
}

