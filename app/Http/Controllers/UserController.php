<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(    ) {
        $this->middleware('auth:api');
    }

    public function registerUsers()
    {
        $errors = [];
        $att = $this->validateCSV();

        $att['file']->move('uploads/csv-files/', $att['file']->getClientOriginalName());

        $handle = fopen('uploads/csv-files/'.$att['file']->getClientOriginalName(), 'r');
        $header = true;

        $i = 0;
        while ($csvLine = fgetcsv($handle, 0, ',')) {
            if ($header) {
                if (!$this->validateHeaders($csvLine)) {
                    return response()->json([
                        'success' => false,
                        'title' => 'ERROR',
                        'message' => 'Por favor asegurese de utilizar la plantilla correcta'
                    ]);
                }
                $header = false;
            } else {
                $user = new User;
                $user->nombres = $csvLine[0];
                $user->apellidos = $csvLine[1];
                $user->email = $csvLine[2];
                $user->num_placa = $csvLine[3];
                $user->edificio = $csvLine[4];
                $user->no_slot = $csvLine[5];
                $user->$rol = $csvLine[6];

                $validator = $this->validateCSVColumns($user);

                if (!$validator->fails()) {
                    $user = $this->unsetNotUsedFields($user);
                    $user->save();
                } else {
                    $errors[$i] = $validator->errors();
                }
            }
            $i++;
        }
        return response()->json([
            'success' => true,
            'message' => 'created',
            'uploaded' => ($i - 1) - count($errors),
            'processed' => ($i - 1),
            'failed' => count($errors),
            'errors' => $errors
        ]);
    }

    /**
     * CSV File validation
     *
     * @return Request
     */
    protected function validateCSV()
    {
        return request()->validate([
            'file' => ['required', 'mimes:csv,txt']
        ]);
    }

    /**
     * CSV Headers validation
     *
     * @param array $headers
     * @return Boolean
     */
    protected function validateHeaders(Array $headers)
    {
        $admitted_headers = [
            'Nombres',
            'Apellidos',
            'E-mail',
            'Placa',
            'Edificio',
            'Numero de Slot',
            'Rol'
        ];

        return $admitted_headers === $headers;
    }

    protected function validateCSVColumns($data)
    {
        return Validator::make($data->toArray(), [
            'nombres' => ['required', 'string', 'max:75'],
            'apellidos' => ['required', 'string', 'max:75'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'num_placa' => ['required', 'string', 'max:15'],
            'edificio' => ['required', 'string', 'max:60'],
            'no_slot' => ['required', 'numeric'],
            'rol' => ['required', 'string', 'max:75'],
        ]);
    }
}
