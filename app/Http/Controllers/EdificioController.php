<?php

namespace App\Http\Controllers;

use App\Edificio;
use Illuminate\Http\Request;

class EdificioController extends Controller
{
    public function index()
    {
        return Edificio::with('reservas')->get();
    }

    public function list()
    {
        return Edificio::select(['id', 'nombre'])->get();
    }

    public function show(Request $request)
    {
        return Edificio::with('reservas')->find($request->id);
    }
}
