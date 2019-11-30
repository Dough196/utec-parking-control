<?php

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
        return response()->json([
            'data' => $request->user()
        ], 200);
    });

    Route::get('/roles', 'RolController@index');

    Route::post('/register', 'Auth\RegisterController@register');
    Route::post('/perfil/actualizar-contrasena', 'Auth\PasswordController@updatePassword');
    Route::get('/users', 'UserController@index');
    Route::get('/users/{id}', 'UserController@show');
    Route::get('/users-por-rol/{rol}', 'UserController@indexByRol');
    Route::get('/users-por-placa', 'UserController@getUserByPlaca');
    Route::post('/users/validar-entrada', 'UserController@validateEntry');
    Route::post('/users/validar-salida', 'UserController@validateDeparture');
    Route::post('/register-users', 'UserController@registerUsers');
    Route::post('/users/asignar-vigilante-edificio', 'UserController@assignBuildingToVigilant');
    
    Route::get('/edificios', 'EdificioController@index');
    Route::get('/lista-edificios', 'EdificioController@list');
    Route::get('/edificios/{id}', 'EdificioController@show');

    Route::get('/reservas', 'ReservaController@indexReservas');
    Route::get('/asignaciones', 'ReservaController@indexAsignaciones');
    Route::get('/reservas/{id}', 'ReservaController@show');
    Route::get('/asignaciones/{id}', 'ReservaController@show');
    Route::post('/asignar-parqueo', 'ReservaController@assignParking');
    Route::post('/asignar-parqueo-alumno', 'ReservaController@assignParkingToStudent');
    // Route::post('/reservar-parqueo', 'ReservaController@bookParking');
    Route::post('/reservar-parqueo', 'ReservaController@bookDayParking');

    Route::get('/historial', 'HistorialController@index');
    Route::get('/historial/{id}', 'HistorialController@show');

});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('/login', 'Auth\LoginController@login');
});