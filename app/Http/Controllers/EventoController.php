<?php

namespace App\Http\Controllers;

use App\Evento;
use App\Horario;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        return response()->json([
            'eventos' => Evento::with('user')
                ->with('edificio')
                ->with('horario')
                ->where('estado', 1)
                ->where('fecha', '>=', now()->toDateString())
                ->get()
        ], 200);
    }

    public function show(Request $request)
    {
        return response()->json([
            'evento' => Evento::with('user')->with('edificio')->with('horario')->find($request->id)
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'edificio_id' => ['required', 'integer', 'exists:edificios,id'],
            'cantidad' => ['required', 'numeric', 'integer', 'min:0'],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora_entrada' => ['required', 'date_format:H:i'],
            'hora_salida' => ['required', 'date_format:H:i'],
            'comentario' => ['nullable', 'string', 'max:100']
        ]);

        $evento = Evento::create([
            'user_id' => auth()->user()->id,
            'edificio_id' => $request->edificio_id,
            'cantidad' => $request->cantidad,
            'fecha' => $request->fecha,
            'estado' => 1,
            'comentario' => $request->comentario
        ]);

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'];
        $fecha = Carbon::createFromFormat('Y-m-d H:i', $request->fecha . ' ' . $request->hora_entrada);

        $evento->horario()->save(new Horario([
            'dia' => $dias[$fecha->dayOfWeek],
            'num_dia' => $fecha->dayOfWeek,
            'hora_entrada' => $request->hora_entrada,
            'hora_salida' => $request->hora_salida
        ]));

        return response()->json([
            'evento' => $evento
        ], 200);
    }
}
