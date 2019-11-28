<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $fillable = ['reserva_id', 'dia', 'num_dia', 'hora_entrada', 'hora_salida'];

    public function reserva()
    {
        return $this->belongsTo('App\Reserva');
    }
}
