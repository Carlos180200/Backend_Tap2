<?php

use App\Http\Controllers\JWTController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register',[JWTController::class, 'registrer'])->name('registrar');
Route::post('login',[JWTController::class,'login'])->name('login');


// Ruta para la verificación de cuenta (fuera del grupo middleware 'jwt.verify')
Route::get('verificar/{token}', [JWTController::class, 'verificar'])->name('verificar');
Route::get('restablecer/{id}', [JWTController::class, 'RestablecerPassword'])->name('restablecer_password');
Route::get('password/{id}', [JWTController::class, 'RecuperarPassword'])->name('password');
Route::post('Newpassword/', [JWTController::class, 'ModificarPassword'])->name('new_password');
Route::post('DestroyUsers/', [JWTController::class, 'destroyUsers'])->name('delete_users');



//proteger rutas
Route::middleware('jwt.verify')->group(function(){
    Route::get('index',[UserController::class,'index']);
});