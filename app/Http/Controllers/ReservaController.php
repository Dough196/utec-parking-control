<?php

namespace App\Http\Controllers;

use App\User;
use App\Horario;
use App\Reserva;
use App\Edificio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservaController extends Controller
{
    public function indexAsignaciones()
    {
        return response()->json([
            'reservas' => Reserva::with('users')
                ->with('edificios')
                ->with('horarios')
                ->where('estado', 1)
                ->whereNull('fecha')
                ->get()
        ], 200);
    }

    public function indexReservas()
    {
        return response()->json([
            'reservas' => Reserva::with('users')
                ->with('edificios')
                ->with('horarios')
                ->where('estado', 1)
                ->whereNotNull('fecha')
                ->where('fecha', now()->toDateString())
                ->get()
        ], 200);
    }

    public function show(Request $request)
    {
        return response()->json([
            'reserva' => Reserva::with('users')->with('edificios')->with('horarios')->find($request->id)
        ], 200);
    }

    public function assignParking(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id'],
            'hora_entrada_lunes' => [
                Rule::requiredIf(function() use($request){
                    return !isset($request['hora_entrada_martes']) &&
                    !isset($request['hora_entrada_miercoles']) &&
                    !isset($request['hora_entrada_jueves']) &&
                    !isset($request['hora_entrada_viernes']) &&
                    !isset($request['hora_entrada_sabado']) &&
                    !isset($request['hora_entrada_domingo']);
                }),
                'array'
            ],
            'hora_entrada_lunes.*' => ['nullable', 'date_format:H:i'],
            'hora_salida_lunes' => ['required_with:hora_entrada_lunes'],
            'hora_salida_lunes.*' => ['nullable', 'date_format:H:i'],
            'hora_entrada_martes' => [
                Rule::requiredIf(function() use($request){
                    return !isset($request['hora_entrada_lunes']) &&
                    !isset($request['hora_entrada_miercoles']) &&
                    !isset($request['hora_entrada_jueves']) &&
                    !isset($request['hora_entrada_viernes']) &&
                    !isset($request['hora_entrada_sabado']) &&
                    !isset($request['hora_entrada_domingo']);
                }),
                'array'
            ],
            'hora_entrada_martes.*' => ['nullable', 'date_format:H:i'],
            'hora_salida_martes' => ['required_with:hora_entrada_martes'],
            'hora_salida_martes.*' => ['nullable', 'date_format:H:i'],
            'hora_entrada_miercoles' => [
                Rule::requiredIf(function() use($request){
                    return !isset($request['hora_entrada_lunes']) &&
                    !isset($request['hora_entrada_martes']) &&
                    !isset($request['hora_entrada_jueves']) &&
                    !isset($request['hora_entrada_viernes']) &&
                    !isset($request['hora_entrada_sabado']) &&
                    !isset($request['hora_entrada_domingo']);
                }),
                'array'
            ],
            'hora_entrada_miercoles.*' => ['nullable', 'date_format:H:i'],
            'hora_salida_miercoles' => ['required_with:hora_entrada_miercoles'],
            'hora_salida_miercoles.*' => ['nullable', 'date_format:H:i'],
            'hora_entrada_jueves' => [
                Rule::requiredIf(function() use($request){
                    return !isset($request['hora_entrada_lunes']) &&
                    !isset($request['hora_entrada_martes']) &&
                    !isset($request['hora_entrada_miercoles']) &&
                    !isset($request['hora_entrada_viernes']) &&
                    !isset($request['hora_entrada_sabado']) &&
                    !isset($request['hora_entrada_domingo']);
                }),
                'array'
            ],
            'hora_entrada_jueves.*' => ['nullable', 'date_format:H:i'],
            'hora_salida_jueves' => ['required_with:hora_entrada_jueves'],
            'hora_salida_jueves.*' => ['nullable', 'date_format:H:i'],
            'hora_entrada_viernes' => [
                Rule::requiredIf(function() use($request){
                    return !isset($request['hora_entrada_lunes']) &&
                    !isset($request['hora_entrada_martes']) &&
                    !isset($request['hora_entrada_miercoles']) &&
                    !isset($request['hora_entrada_jueves']) &&
                    !isset($request['hora_entrada_sabado']) &&
                    !isset($request['hora_entrada_domingo']);
                }),
                'array'
            ],
            'hora_entrada_viernes.*' => ['nullable', 'date_format:H:i'],
            'hora_salida_viernes' => ['required_with:hora_entrada_viernes'],
            'hora_salida_viernes.*' => ['nullable', 'date_format:H:i'],
            'hora_entrada_sabado' => [
                Rule::requiredIf(function() use($request){
                    return !isset($request['hora_entrada_lunes']) &&
                    !isset($request['hora_entrada_martes']) &&
                    !isset($request['hora_entrada_miercoles']) &&
                    !isset($request['hora_entrada_jueves']) &&
                    !isset($request['hora_entrada_viernes']) &&
                    !isset($request['hora_entrada_domingo']);
                }),
                'array'
            ],
            'hora_entrada_sabado.*' => ['nullable', 'date_format:H:i'],
            'hora_salida_sabado' => ['required_with:hora_entrada_sabado'],
            'hora_salida_sabado.*' => ['nullable', 'date_format:H:i'],
            'hora_entrada_domingo' => [
                Rule::requiredIf(function() use($request){
                    return !isset($request['hora_entrada_lunes']) &&
                    !isset($request['hora_entrada_martes']) &&
                    !isset($request['hora_entrada_miercoles']) &&
                    !isset($request['hora_entrada_jueves']) &&
                    !isset($request['hora_entrada_viernes']) &&
                    !isset($request['hora_entrada_sabado']);
                }),
                'array'
            ],
            'hora_entrada_domingo.*' => ['nullable', 'date_format:H:i'],
            'hora_salida_domingo' => ['required_with:hora_entrada_domingo'],
            'hora_salida_domingo.*' => ['nullable', 'date_format:H:i']
        ]);

        $reserva = Reserva::create([
            'estado' => 1
        ]);

        if(isset($request->hora_entrada_lunes)) {
            for ($i=0; $i < count($request->hora_entrada_lunes); $i++) {
                if (!empty($request->hora_entrada_lunes[$i]) && !empty($request->hora_salida_lunes[$i])) {
                    $reserva->horarios()->save(new Horario([
                        'dia' => 'Lunes',
                        'num_dia' => 1,
                        'hora_entrada' => $request->hora_entrada_lunes[$i],
                        'hora_salida' => $request->hora_salida_lunes[$i]
                    ]));                    
                }
            }
        }

        if(isset($request->hora_entrada_martes)) {
            for ($i=0; $i < count($request->hora_entrada_martes); $i++) { 
                if (!empty($request->hora_entrada_martes[$i]) && !empty($request->hora_salida_martes[$i])) {
                    $reserva->horarios()->save(new Horario([
                        'dia' => 'Martes',
                        'num_dia' => 2,
                        'hora_entrada' => $request->hora_entrada_martes[$i],
                        'hora_salida' => $request->hora_salida_martes[$i]
                    ]));
                }
            }
        }

        if(isset($request->hora_entrada_miercoles)) {
            for ($i=0; $i < count($request->hora_entrada_miercoles); $i++) {
                if (!empty($request->hora_entrada_miercoles[$i]) && !empty($request->hora_salida_miercoles[$i])) {
                    $reserva->horarios()->save(new Horario([
                        'dia' => 'Miercoles',
                        'num_dia' => 3,
                        'hora_entrada' => $request->hora_entrada_miercoles[$i],
                        'hora_salida' => $request->hora_salida_miercoles[$i]
                    ]));
                }
            }
        }

        if(isset($request->hora_entrada_jueves)) {
            for ($i=0; $i < count($request->hora_entrada_jueves); $i++) {
                if (!empty($request->hora_entrada_jueves[$i]) && !empty($request->hora_salida_jueves[$i])) {
                    $reserva->horarios()->save(new Horario([
                        'dia' => 'Jueves',
                        'num_dia' => 4,
                        'hora_entrada' => $request->hora_entrada_jueves[$i],
                        'hora_salida' => $request->hora_salida_jueves[$i]
                    ]));
                }
            }
        }

        if(isset($request->hora_entrada_viernes)) {
            for ($i=0; $i < count($request->hora_entrada_viernes); $i++) {
                if (!empty($request->hora_entrada_viernes[$i]) && !empty($request->hora_salida_viernes[$i])) {
                    $reserva->horarios()->save(new Horario([
                        'dia' => 'Viernes',
                        'num_dia' => 5,
                        'hora_entrada' => $request->hora_entrada_viernes[$i],
                        'hora_salida' => $request->hora_salida_viernes[$i]
                    ]));
                }
            }
        }

        if(isset($request->hora_entrada_sabado)) {
            for ($i=0; $i < count($request->hora_entrada_sabado); $i++) {
                if (!empty($request->hora_entrada_sabado[$i]) && !empty($request->hora_salida_sabado[$i])) {
                    $reserva->horarios()->save(new Horario([
                        'dia' => 'Sábado',
                        'num_dia' => 6,
                        'hora_entrada' => $request->hora_entrada_sabado[$i],
                        'hora_salida' => $request->hora_salida_sabado[$i]
                    ]));
                }
            }
        }


        if(isset($request->hora_entrada_domingo)) {
            for ($i=0; $i < count($request->hora_entrada_domingo); $i++) {
                if (!empty($request->hora_entrada_domingo[$i]) && !empty($request->hora_salida_domingo[$i])) {
                    $reserva->horarios()->save(new Horario([
                        'dia' => 'Domingo',
                        'num_dia' => 0,
                        'hora_entrada' => $request->hora_entrada_domingo[$i],
                        'hora_salida' => $request->hora_salida_domingo[$i]
                    ]));
                }
            }
        }

        $reserva->users()->syncWithoutDetaching([$request->user_id => ['edificio_id' => $request->edificio_id]]);

        return response()->json(
            ['reserva' => $reserva],
            200
        );
    }

    public function assignParkingToStudent(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id']
        ]);
        
        $user = User::with('reservas.edificios')->find($request->user_id);

        if ($user->rol_id != 4) {
            return response()->json(['error' => 'El usuario debe de ser un estudiante'], 401);
        }

        $reserva = Reserva::create([
            'estado' => 1
        ]);
        $user->reservas()->sync([$reserva->id => ['edificio_id' => $request->edificio_id]]);
        $user->estado = 1;
        $user->save();

        return response()->json(
            ['usuario' => $user],
            200
        );
    }

    // public function bookParking(Request $request)
    // {
    //     $this->validate($request, [
    //         'user_id' => ['required', 'integer', 'exists:users,id'],
    //         'edificio_id' => ['required', 'integer', 'exists:edificios,id']
    //     ]);

    //     $edificio = Edificio::find($request->edificio_id);
    //     $edificio->num_disponible = $edificio->num_disponible - 1;
    //     $edificio->num_reservados = $edificio->num_reservados + 1;
    //     $edificio->reservas()->save(new Reserva([
    //         'estado' => 1,
    //         'num_slot' => $edificio->slots_disponibles[0]
    //     ]));
    //     $edificio->save();

    //     $user = User::find($request->user_id);
    //     $user->reserva_id = $edificio->reservas()->latest()->first()->id;

    //     $user->save();
    //     return response()->json(
    //         ['usuario' => $user],
    //         200
    //     );
    // }

    public function bookDayParking(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id'],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora_entrada' => ['required', 'date_format:H:i'],
            'hora_salida' => ['required', 'date_format:H:i'],
            'cantidad' => ['required', 'numeric', 'integer', 'min:0'],
            'comentario' => ['nullable', 'string', 'max:100']
        ]);

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'];
        $fecha = Carbon::createFromFormat('Y-m-d H:i', $request->fecha . ' ' . $request->hora_entrada);
      
        for ($i=0; $i < $request->cantidad; $i++) { 
            $reserva = Reserva::create([
                'estado' => 1,
                'fecha' => $request->fecha,
                'comentario' => $request->comentario
            ]);
            $reserva->horarios()->save(new Horario([
                'dia' => $dias[$fecha->dayOfWeek],
                'num_dia' => $fecha->dayOfWeek,
                'hora_entrada' => $request->hora_entrada,
                'hora_salida' => $request->hora_salida
            ]));
            $reserva->users()->syncWithoutDetaching([$request->user_id => ['edificio_id' => $request->edificio_id]]);
        }


        return response()->json(
            ['reservas' => Reserva::latest()->take($request->cantidad)->get()],
            200
        );
    }
}
