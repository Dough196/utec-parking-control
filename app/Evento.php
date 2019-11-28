<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = ['edificio_id', 'horario_id', 'user_id', 'num_reservas', 'fecha', 'motivo'];
}
