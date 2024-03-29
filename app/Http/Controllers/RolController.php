<?php

namespace App\Http\Controllers;

use App\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        return response()->json([
            'roles' => Rol::select('id', 'nombre')->get()
        ], 200);
    }
}
