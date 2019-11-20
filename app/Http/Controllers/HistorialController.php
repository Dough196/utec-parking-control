<?php

namespace App\Http\Controllers;

use App\Historial;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index()
    {
        return response()->json([
            'historial' => Historial::with('user')->with('edificio')->get()
        ], 200);
    }

    public function show(Request $request)
    {
        return response()->json([
            'historial' => Historial::with('user')->with('edificio')->find($request->id)
        ], 200);
    }
}
