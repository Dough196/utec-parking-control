<?php

namespace App\Http\Controllers;

use App\Reserva;
use Illuminate\Http\Request;

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
}
