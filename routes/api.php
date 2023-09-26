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
Route::get('verificar/{id}', [JWTController::class, 'verificar'])->name('verificar');


//proteger rutas
Route::middleware('jwt.verify')->group(function(){
    Route::get('index',[UserController::class,'index']);
});