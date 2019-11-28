<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    protected $table = 'historial';

    protected $fillable = ['reserva_id', 'comentario', 'entrada', 'salida', 'fecha', 'calificacion'];

    public function reserva()
    {
        return $this->belongsTo('App\Reserva');
    }
}
