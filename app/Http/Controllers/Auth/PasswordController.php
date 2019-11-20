<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $att = $this->validatePasswords();

        if (!Hash::check($att['password'], auth()->user()->password)) {
            return response()->json([
                'error' => 'Los datos no coinciden con nuestros registros'
            ], 404);
        }

        $user = User::find(auth()->user()->id);
        $user->password = Hash::make($att['new_password']);
        $user->update();

        return response()->json([
            'success' => 'Contraseña actualizada exitosamente'
        ], 200);
    }

    protected function validatePasswords()
    {
        return request()->validate([
            'password' => 'required',
            'new_password' => 'required|confirmed|different:password'
        ]);
    }

}
