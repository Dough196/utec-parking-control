<?php

namespace App\Http\Controllers;

use App\User;
use App\Reserva;
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
        return response()->json([
            'usuarios' => User::with('rol')->with('reservas.edificios')->get()
        ], 200);
    }

    public function indexByRol(Request $request)
    {
        switch ($request->rol) {
            case 'admin':
                return response()->json([
                    'usuarios' => User::with('rol')->with('reservas.edificios')->whereHas('rol', function ($q) {
                        $q->where('id', 1);
                    })->get()
                ], 200);
                break;
            case 'personal-admin':
                return response()->json([
                    'usuarios' => User::with('rol')->with('reservas.edificios')->whereHas('rol', function ($q) {
                        $q->where('id', 2);
                    })->get()
                ], 200);
                break;
            case 'docente':
                return response()->json([
                    'usuarios' => User::with('rol')->with('reservas.edificios')->whereHas('rol', function ($q) {
                        $q->where('id', 3);
                    })->get()
                ], 200);
                break;
            case 'alumno':
                return response()->json([
                    'usuarios' => User::with('rol')->with('reservas.edificios')->whereHas('rol', function ($q) {
                        $q->where('id', 4);
                    })
                    ->get()
                ], 200);
                break;    
            case 'alumno-activo':
                return response()->json([
                    'usuarios' => User::with('rol')->with('reservas.edificios')->whereHas('rol', function ($q) {
                        $q->where('id', 4);
                    })
                    ->where('estado', 1)
                    ->get()
                ], 200);
                break;
            case 'alumno-inactivo':
                return response()->json([
                    'usuarios' => User::with('rol')->with('reservas.edificios')->whereHas('rol', function ($q) {
                        $q->where('id', 4);
                    })
                    ->where('estado', 0)
                    ->get()
                ], 200);
                break;
            case 'vigilante':
                return response()->json([
                    'usuarios' => User::with('edificios')->with('rol')->whereHas('rol', function ($q) {
                            $q->where('id', 5);
                        })->get()
                ], 200);
                break;
            default:
                return response()->json([
                    'error' => 'Rol inválido'
                ], 404);
                break;
        }
    }

    public function show(Request $request)
    {
        $user = User::with('rol')
            ->with('reservas.edificios')
            ->with('edificios')
            ->find($request->id);
        if ($user->rol_id != 5) {
            unset($user->edificios);
        }
        if ($user->rol_id == 5) {
            unset($user->reservas);
        }
        return response()->json([
            'usuario' => $user
        ], 200);
    }

    public function getUserByPlaca(Request $request)
    {
        $this->validate($request, [
            'num_placa' => ['required', 'string', 'max:15']
        ]);
        $user = User::with(['reservas.edificios', 'reservas.historial', 'reservas.horarios'])->where('num_placa', $request->num_placa)->first();

        return response()->json([
            'usuario' => $user
        ], 200);
    }

    public function validateEntry(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id']
        ]);
        
        $user = User::find($request->user_id);

        $validateSchedule = $user->rol_id != 4 ? true : null;

        $reserva = Reserva::with('users')
            ->with('horarios')
            ->with('edificios')
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user_id);
            })
            ->when($validateSchedule, function ($q) {
                return $q->whereHas('horarios', function ($q) {
                    $q->where('num_dia', now()->dayOfWeek);
                });
            })
            ->where('estado', 1)->first();
        
        if(!$reserva) {
            return response()->json(['message' => 'No se encontró una reserva válida o una reserva para el día de hoy'], 401);
        }

        if(!$reserva->lastHistorial() || ($reserva->lastHistorial()->entrada && $reserva->lastHistorial()->salida)) {
            $fecha_reserva = $reserva->fecha ? Carbon::createFromFormat('Y-m-d', $reserva->fecha) : null;
            if ($fecha_reserva && now()->diffInHours($fecha_reserva, false) < 0) {
                $reserva->estado = 0;
                $reserva->save();
                return response()->json(['message' => 'No se encontró una reserva válida o una reserva para el día de hoy'], 401);
            }
    
            $hasValidSchedule = false;

            if ($validateSchedule) {
                foreach ($reserva->horarios as $horario) {
                    // dd(Carbon::createFromFormat('H:s:i', $horario->hora_entrada));
                    $fecha_entrada = Carbon::createFromFormat('H:s:i', $horario->hora_entrada);
                    $fecha_salida = Carbon::createFromFormat('H:s:i', $horario->hora_salida);
                    if (now()->diffInMinutes($fecha_entrada, false) <= 30 && now()->diffInMinutes($fecha_salida, false) > 10) {
                        $hasValidSchedule = true;
                    }
                }
            } else {
                $hasValidSchedule = true;
            }
            if (!$hasValidSchedule) {
                return response()->json([
                    'message' => 'No se encontro una reserva para esta hora',
                    'nota' => 'Sólo permite la entrada 30 minutos antes de la hora de entrada estipulada y diez minutos antes de la hora de salida'
                ], 401);
            }
            if (!empty($reserva->fecha)) {
                $reserva->estado = 0;
                $reserva->save();
            }
    
            $reserva->historial()->save(new Historial([
                'edificio_id' => $request->edificio_id,
                'fecha' => now()->toDateString(),
                'entrada' => now()->toTimeString()
            ]));

            $edificio = Edificio::find($request->edificio_id);
            $edificio->num_disponible = $edificio->num_disponible - 1;
            $edificio->num_ocupado = $edificio->num_ocupado + 1;
            $edificio->save();
    
            return response()->json([
                'message' => 'Validacion de entrada exitosa'
            ], 200);
        } elseif ($reserva->lastHistorial()->entrada && !$reserva->lastHistorial()->salida) {
            return response()->json(['message' => 'Usuario ya se encuentra en el parqueo'], 200);
        } else {
            return response()->json(['message' => 'Hubo por favor intente más tarde'], 200);
        }
    }

    public function validateDeparture(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'comentario' => ['nullable', 'string', 'max:100'],
            'calificacion' => ['nullable', 'numeric', 'integer', 'between:0,5']
        ]);

        $reserva = Reserva::with('users')
            ->with('historial')
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user_id);
            })
            ->whereHas('historial', function ($q) {
                $q->whereNotNull('entrada')
                ->whereNull('salida');
            })
            ->first();
        
        if (!$reserva) {
            return response()->json(['message' => 'No se ha registrado una entrada para este usuario'], 200);
        }

        $reserva->historial[count($reserva->historial) - 1]->salida = now()->toTimeString();
        $reserva->historial[count($reserva->historial) - 1]->comentario = isset($request->comentario) ?  $request->comentario : null;
        $reserva->historial[count($reserva->historial) - 1]->calificacion = isset($request->calificacion) ?  $request->calificacion : null;
        $reserva->push();

        $edificio = Edificio::find($reserva->historial[count($reserva->historial) - 1]->edificio_id);
        $edificio->num_disponible = $edificio->num_disponible + 1;
        $edificio->num_ocupado = $edificio->num_ocupado - 1;
        $edificio->save();

        return response()->json([
            'message' => 'Validacion de salida exitosa'
        ], 200);
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

    public function assignBuildingToVigilant(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id']
        ]);
        
        $user = User::with('edificios')->find($request->user_id);

        if ($user->rol_id != 5) {
            return response()->json(['error' => 'El usuario debe de ser un vigilante'], 401);
        }

        $user->edificios()->sync([$request->edificio_id]);

        return response()->json(
            ['usuario' => $user],
            200
        );
    }
}
