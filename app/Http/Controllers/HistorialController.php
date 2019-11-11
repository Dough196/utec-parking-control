<?php

namespace App\Http\Controllers;

use App\Historial;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index()
    {
        return Historial::with('user')->with('edificio')->get();
    }

    public function show(Request $request)
    {
        return Historial::with('user')->with('edificio')->find($request->id);
    }
}
