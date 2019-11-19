<?php

namespace App\Http\Controllers;

use App\User;
use App\Edificio;
use App\Historial;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(    ) {
        $this->middleware('auth:api');
    }

    public function index()
    {
        return User::with('rol')->with('horarios')->get();
    }

    public function show(Request $request)
    {
        return User::with('rol')->with('horarios')->find($request->id);
    }

    public function getUserByPlaca(Request $request)
    {
        $this->validate($request, [
            'num_placa' => ['required', 'string', 'max:15']
        ]);
        $user = User::with('reserva')->with('horarios')->with('historial')->where('num_placa', $request->num_placa)->first();

        return response()->json($user);
    }

    public function validateEntry(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id']
        ]);

        $user = User::with(['historial' => function ($query) { 
                $query->latest()->first();
        }])->find($request->user_id);

        if (!count($user->historial) || ($user->historial[0]->entrada && $user->historial[0]->salida)) {
            $user->historial()->save(new Historial([
                'edificio_id' => $request->edificio_id,
                'entrada' => Carbon::now()->toTimeString(),
                'fecha' => Carbon::today()->toDateString()
            ]));
            $edificio = Edificio::find($request->edificio_id);
            $edificio->num_disponible = $edificio->num_disponible - 1;
            $edificio->num_ocupado = $edificio->num_ocupado + 1;
            $edificio->save();
            return $user;
        } elseif ($user->historial[0]->entrada && !$user->historial[0]->salida) {
            return response()->json(['message' => 'Usuario ya se encuentra en el parqueo'], 200);
        } else {
            return response()->json(['message' => 'Hubo un error comuniquese con el administrador'], 200);
        }
    }

    public function validateDeparture(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'comentario' => ['nullable', 'string', 'max:100']
        ]);

        $user = User::with(['historial' => function ($query) { 
            $query->latest()->first();
        }])->find($request->user_id);

        if (count($user->historial) && ($user->historial[0]->entrada && !$user->historial[0]->salida)) {
            $user->historial[0]->salida = Carbon::now()->toTimeString();
            $user->historial[0]->comentario = isset($request->comentario) ?  $request->comentario : null;
            $user->push();

            $edificio = Edificio::find($user->historial[0]->edificio_id);
            $edificio->num_disponible = $edificio->num_disponible + 1;
            $edificio->num_ocupado = $edificio->num_ocupado - 1;
            $edificio->save();
            return $user;
        } else {
            return response()->json(['message' => 'No se ha registrado una entrada para este usuario'], 200);
        }
    }

    public function registerUsers()
    {
        $errors = [];
        $att = $this->validateCSV();

        $att['file']->move('uploads/csv-files/', $att['file']->getClientOriginalName());

        $handle = fopen('uploads/csv-files/'.$att['file']->getClientOriginalName(), 'r');
        $header = true;

        $i = 0;
        while ($csvLine = fgetcsv($handle, 0, ',')) {
            if ($header) {
                if (!$this->validateHeaders($csvLine)) {
                    return response()->json([
                        'success' => false,
                        'title' => 'ERROR',
                        'message' => 'Por favor asegurese de utilizar la plantilla correcta'
                    ]);
                }
                $header = false;
            } else {
                $user = new User;
                $user->nombres = $csvLine[0];
                $user->apellidos = $csvLine[1];
                $user->email = $csvLine[2];
                $user->num_placa = $csvLine[3];
                $user->edificio = $csvLine[4];
                $user->no_slot = $csvLine[5];
                $user->$rol = $csvLine[6];

                $validator = $this->validateCSVColumns($user);

                if (!$validator->fails()) {
                    $user = $this->unsetNotUsedFields($user);
                    $user->save();
                } else {
                    $errors[$i] = $validator->errors();
                }
            }
            $i++;
        }
        return response()->json([
            'success' => true,
            'message' => 'created',
            'uploaded' => ($i - 1) - count($errors),
            'processed' => ($i - 1),
            'failed' => count($errors),
            'errors' => $errors
        ]);
    }

    /**
     * CSV File validation
     *
     * @return Request
     */
    protected function validateCSV()
    {
        return request()->validate([
            'file' => ['required', 'mimes:csv,txt']
        ]);
    }

    /**
     * CSV Headers validation
     *
     * @param array $headers
     * @return Boolean
     */
    protected function validateHeaders(Array $headers)
    {
        $admitted_headers = [
            'Nombres',
            'Apellidos',
            'E-mail',
            'Placa',
            'Edificio',
            'Numero de Slot',
            'Rol'
        ];

        return $admitted_headers === $headers;
    }

    protected function validateCSVColumns($data)
    {
        return Validator::make($data->toArray(), [
            'nombres' => ['required', 'string', 'max:75'],
            'apellidos' => ['required', 'string', 'max:75'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'num_placa' => ['required', 'string', 'max:15'],
            'edificio' => ['required', 'string', 'max:60'],
            'no_slot' => ['required', 'numeric'],
            'rol' => ['required', 'string', 'max:75'],
        ]);
    }
}
