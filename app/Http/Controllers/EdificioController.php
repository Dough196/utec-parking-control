<?php

namespace App\Http\Controllers;

use App\Edificio;
use Illuminate\Http\Request;

class EdificioController extends Controller
{
    public function index()
    {
        return response()->json([
            'edificios' => Edificio::with('reservas')->get()
        ], 200);
    }

    public function list()
    {
        return response()->json([
            'edificios' => Edificio::select(['id', 'nombre'])->get()
        ], 200);
    }

    public function show(Request $request)
    {
        return response()->json([
            'edificio' => Edificio::with('reservas')->find($request->id)
        ], 200);
    }
}
