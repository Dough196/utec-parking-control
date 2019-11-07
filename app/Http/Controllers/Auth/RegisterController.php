<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Reserva;
use App\Edificio;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol_id' => ['nullable', 'integer', 'exists:roles,id'],
            'num_placa' => ['required_with:edificio_id', 'string', 'max:15'],
            'edificio_id' => ['required_with:num_slot,num_placa', 'integer', 'exists:edificios,id'],
            'num_slot' => ['nullable', 'integer'],
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
        if (!isset($data['num_placa'])  && !isset($data['edificio_id'])) {
            return User::create([
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'estado' => 1,
                'rol_id' => 1,
                'api_token' => Str::random(80)
            ]);
        } else {
            $edificio = Edificio::find($data['edificio_id']);
            $edificio->num_disponible = $edificio->num_disponible - 1;    
            $edificio->num_ocupado = $edificio->num_ocupado + 1;
            $edificio->num_reservados = isset($data['num_slot']) ? $edificio->num_reservados + 1 : $edificio->num_reservados;
            $edificio->reservas()->save(new Reserva([
                'estado' => 1,
                'num_slot' => isset($data['num_slot']) ? $data['num_slot'] : null
            ]));
            $edificio->save();
            return User::create([
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'estado' => 1,
                'rol_id' => $data['rol_id'],
                'reserva_id' => $edificio->reservas()->latest()->first()->id,
                'api_token' => Str::random(80)
            ]);
        }
    }

    protected function registered(Request $request, $user)
    {
        $user->generateToken();

        return response()->json(['data' => $user->toArray()], 201);
    }
}
