<?php

namespace App\Http\Controllers;

use App\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index()
    {
        return Reserva::with('user')->with('edificio')->where('num_slot', '<>', 0)->where('num_slot', '<>', null)->get();
    }

    public function show(Request $request)
    {
        return Reserva::with('user')->with('edificio')->where('num_slot', '<>', 0)->where('num_slot', '<>', null)->find($request->id);
    }
}
