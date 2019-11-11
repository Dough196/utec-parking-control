<?php

use App\Rol;
use Illuminate\Http\Request;

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

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', function(Request $request) {
        return $request->user();
    });
    Route::post('/register', 'Auth\RegisterController@register');
    Route::get('/users', 'UserController@index');
    Route::get('/users/{id}', 'UserController@show');
    Route::get('/users-por-placa', 'UserController@getUserByPlaca');
    Route::post('/users/validar-entrada', 'UserController@validateEntry');
    Route::get('/users/validar-salida', 'UserController@validateDeparture');
    Route::post('/register-users', 'UserController@registerUsers');
    Route::get('/edificios', 'EdificioController@index');
    Route::get('/lista-edificios', 'EdificioController@list');
    Route::get('/edificios/{id}', 'EdificioController@show');
    Route::get('/reservas', 'ReservaController@index');
    Route::get('/reservas/{id}', 'ReservaController@show');
    Route::get('/historial', 'HistorialController@index');
    Route::get('/historial', 'HistorialController@index');
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('/login', 'Auth\LoginController@login');
});