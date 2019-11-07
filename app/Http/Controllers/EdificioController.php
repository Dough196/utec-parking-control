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

    public function show(Request $request)
    {
        return Edificio::with('reservas')->findOrFail($request->id);
    }
}
