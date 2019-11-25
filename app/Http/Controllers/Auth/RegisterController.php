<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Horario;
use App\Reserva;
use App\Edificio;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nombres' => ['required', 'string', 'max:75'],
            'apellidos' => ['required', 'string', 'max:75'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'carnet' => ['required_if:rol_id,3', 'string', 'min:12', 'max:12', 'unique:users,carnet'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            'num_placa' => [
                Rule::requiredIf(function() use($data){
                    return isset($data['edificio_id']) ||
                    in_array($data['rol_id'], [1, 2, 3]);
                }),
                'string',
                'max:15',
                'unique:users,num_placa'
            ],
            'edificio_id' => [
                Rule::requiredIf(function() use($data){
                    return isset($data['num_placa']) &&
                    in_array($data['rol_id'], [1, 2, 3]);
                }),
                'integer',
                'exists:edificios,id'
            ]
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        if ($data['rol_id'] == 4) {
            return User::create([
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'estado' => 1,
                'rol_id' => $data['rol_id'],
                'api_token' => Str::random(80)
            ]);
        } else {
            $edificio = Edificio::find($data['edificio_id']);
            // $edificio->num_disponible = isset($data['num_slot']) ? $edificio->num_disponible - 1 : $edificio->num_disponible;
            // $edificio->num_reservados = isset($data['num_slot']) ? $edificio->num_reservados + 1 : $edificio->num_reservados;
            $edificio->reservas()->save(new Reserva([
                'estado' => 0
            ]));
            $edificio->save();

            $user = User::create([
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'carnet' => isset($data['carnet']) ? $data['carnet'] : null,
                'num_placa' => $data['num_placa'],
                'password' => Hash::make($data['password']),
                'estado' => 1,
                'rol_id' => $data['rol_id'],
                'reserva_id' => $edificio->reservas()->latest()->first()->id,
                'api_token' => Str::random(80)
            ]);

            return $user;
        }
    }

    protected function registered(Request $request, $user)
    {
        $user->generateToken();

        return response()->json(['data' => $user->toArray()], 201);
    }
}
