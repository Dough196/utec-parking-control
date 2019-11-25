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
    public function index()
    {
        return response()->json([
            'reservas' => Reserva::with('user')->with('edificio')->where('num_slot', '<>', 0)->where('num_slot', '<>', null)->get()
        ], 200);
    }

    public function show(Request $request)
    {
        return response()->json([
            'reserva' => Reserva::with('user')->with('edificio')->where('num_slot', '<>', 0)->where('num_slot', '<>', null)->find($request->id)
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
            'hora_entrada_lunes.*' => ['sometimes', 'date_format:H:i'],
            'hora_salida_lunes' => ['required_with:hora_entrada_lunes'],
            'hora_salida_lunes.*' => ['sometimes', 'date_format:H:i'],
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
            'hora_entrada_martes.*' => ['sometimes', 'date_format:H:i'],
            'hora_salida_martes' => ['required_with:hora_entrada_martes'],
            'hora_salida_martes.*' => ['sometimes', 'date_format:H:i'],
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
            'hora_entrada_miercoles.*' => ['sometimes', 'date_format:H:i'],
            'hora_salida_miercoles' => ['required_with:hora_entrada_miercoles'],
            'hora_salida_miercoles.*' => ['sometimes', 'date_format:H:i'],
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
            'hora_entrada_jueves.*' => ['sometimes', 'date_format:H:i'],
            'hora_salida_jueves' => ['required_with:hora_entrada_jueves'],
            'hora_salida_jueves.*' => ['sometimes', 'date_format:H:i'],
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
            'hora_entrada_viernes.*' => ['sometimes', 'date_format:H:i'],
            'hora_salida_viernes' => ['required_with:hora_entrada_viernes'],
            'hora_salida_viernes.*' => ['sometimes', 'date_format:H:i'],
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
            'hora_entrada_sabado.*' => ['sometimes', 'date_format:H:i'],
            'hora_salida_sabado' => ['required_with:hora_entrada_sabado'],
            'hora_salida_sabado.*' => ['sometimes', 'date_format:H:i'],
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
            'hora_entrada_domingo.*' => ['sometimes', 'date_format:H:i'],
            'hora_salida_domingo' => ['required_with:hora_entrada_domingo'],
            'hora_salida_domingo.*' => ['sometimes', 'date_format:H:i']
        ]);

        $edificio = Edificio::with('reservas')->find($request->edificio_id);

        $edificio->reservas()->save(new Reserva([
            'estado' => 1,
            'num_slot' => 0
        ]));
        $edificio->save();

        $user = User::with('horarios')->find($request->user_id);
        $user->reserva_id = $edificio->reservas()->latest()->first()->id;

        if(isset($request->hora_entrada_lunes)) {
            for ($i=0; $i < count($request->hora_entrada_lunes); $i++) { 
                $user->horarios()->save(new Horario([
                    'dias' => 'Lunes',
                    'hora_entrada' => $request->hora_entrada_lunes[$i],
                    'hora_salida' => $request->hora_salida_lunes[$i]
                ]));
            }
        }

        if(isset($request->hora_entrada_martes)) {
            for ($i=0; $i < count($request->hora_entrada_martes); $i++) { 
                $user->horarios()->save(new Horario([
                    'dias' => 'Martes',
                    'hora_entrada' => $request->hora_entrada_martes[$i],
                    'hora_salida' => $request->hora_salida_martes[$i]
                ]));
            }
        }

        if(isset($request->hora_entrada_miercoles)) {
            for ($i=0; $i < count($request->hora_entrada_miercoles); $i++) { 
                $user->horarios()->save(new Horario([
                    'dias' => 'Miercoles',
                    'hora_entrada' => $request->hora_entrada_miercoles[$i],
                    'hora_salida' => $request->hora_salida_miercoles[$i]
                ]));
            }
        }

        if(isset($request->hora_entrada_jueves)) {
            for ($i=0; $i < count($request->hora_entrada_jueves); $i++) { 
                $user->horarios()->save(new Horario([
                    'dias' => 'Jueves',
                    'hora_entrada' => $request->hora_entrada_jueves[$i],
                    'hora_salida' => $request->hora_salida_jueves[$i]
                ]));
            }
        }

        if(isset($request->hora_entrada_viernes)) {
            for ($i=0; $i < count($request->hora_entrada_viernes); $i++) { 
                $user->horarios()->save(new Horario([
                    'dias' => 'Viernes',
                    'hora_entrada' => $request->hora_entrada_viernes[$i],
                    'hora_salida' => $request->hora_salida_viernes[$i]
                ]));
            }
        }

        if(isset($request->hora_entrada_sabado)) {
            for ($i=0; $i < count($request->hora_entrada_sabado); $i++) { 
                $user->horarios()->save(new Horario([
                    'dias' => 'Sábado',
                    'hora_entrada' => $request->hora_entrada_sabado[$i],
                    'hora_salida' => $request->hora_salida_sabado[$i]
                ]));
            }
        }


        if(isset($request->hora_entrada_domingo)) {
            for ($i=0; $i < count($request->hora_entrada_domingo); $i++) { 
                $user->horarios()->save(new Horario([
                    'dias' => 'Domingo',
                    'hora_entrada' => $request->hora_entrada_domingo[$i],
                    'hora_salida' => $request->hora_salida_domingo[$i]
                ]));
            }
        }
        $user->save();

        return response()->json(
            ['usuario' => $user],
            200
        );
    }

    public function bookParking(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id']
        ]);

        $edificio = Edificio::find($request->edificio_id);
        $edificio->num_disponible = $edificio->num_disponible - 1;
        $edificio->num_reservados = $edificio->num_reservados + 1;
        $edificio->reservas()->save(new Reserva([
            'estado' => 1,
            'num_slot' => $edificio->slots_disponibles[0]
        ]));
        $edificio->save();

        $user = User::find($request->user_id);
        $user->reserva_id = $edificio->reservas()->latest()->first()->id;

        return response()->json(
            ['usuario' => $user->save()],
            200
        );
    }

    public function bookDayParking(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'edificio_id' => ['required', 'integer', 'exists:edificios,id'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora_entrada' => ['required', 'date_format:H:i'],
            'hora_salida' => ['required', 'date_format:H:i'],
        ]);

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'];
        $fecha = Carbon::createFromFormat('Y-m-d H:i', $request->fecha . ' ' . $request->hora_entrada);

        $edificio = Edificio::find($request->edificio_id);
        $edificio->reservas()->save(new Reserva([
            'estado' => 1,
            'num_slot' => $edificio->slots_disponibles[0],
            'fecha' => $request->fecha
        ]));

        $edificio->save();

        $user = User::with('reserva')->with('horarios')->find($request->user_id);
        $user->reserva_id = $edificio->reservas()->latest()->first()->id;

        $user->horarios()->save(new Horario([
            'dias' => $dias[$fecha->dayOfWeek],
            'hora_entrada' => $request->hora_entrada,
            'hora_salida' => $request->hora_salida
        ]));

        $user->save();

        return response()->json(
            ['usuario' => $user],
            200
        );
    }
}
