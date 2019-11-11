<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $fillable = ['user_id', 'dias', 'hora_entrada', 'hora_salida'];
}
