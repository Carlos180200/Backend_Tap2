<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTController extends Controller
{

    public function view()
    {
        
    }

    
    public function registrer(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = request(['email', 'password']);
        
        try{
            if(!$token = JWTAuth::attempt($credentials)){
                return response()->json([
                    'error' => 'invalid credentials'
                ], 400);
            }
        }catch(JWTException $e){
            return response()->json([
                'error' => 'not create token'
            ],500);
        }
        return response()->json(compact('token'));
    }
}