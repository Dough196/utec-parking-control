<?php

namespace App\Http\Controllers;

use App\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index()
    {
        return Reserva::with('user')->with('edificio')->whereNotNull('num_slot')->get();
    }

    public function show(Request $request)
    {
        return Reserva::with('user')->with('edificio')->whereNotNull('num_slot')->findOrFail($request->id);
    }
}
