<?php

namespace App\Http\Controllers;

use App\Historial;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index()
    {
        return response()->json([
            'historial' => Historial::with('reserva.users')->with('edificio')->get()
        ], 200);
    }

    public function show(Request $request)
    {
        return response()->json([
            'historial' => Historial::with('reserva.users')->with('edificio')->find($request->id)
        ], 200);
    }

    public function addComment(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'comentario' => ['required', 'string', 'max:100'],
        ]);

        $historial = Historial::with('reserva.users')
            ->whereHas('reserva.users', function ($q) use ($request) {
                $q->where('users.id', $request->user_id);
            })
            ->latest()
            ->first();
        
        if (!$historial) {
            return response()->json([
                'message' => 'No se encontró un historial del usuario'
            ], 404);
        }
        
        $historial->comentario = $request->comentario;

        $historial->save();

        return response()->json([
            'historial' => $historial
        ], 200);
    }
}
