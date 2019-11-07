<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Edificio extends Model
{
    protected $fillable = ['alias', 'nombre', 'num_parqueos', 'num_disponibles', 'num_ocupados', 'num_reservados'];

    public function reservas()
    {
        return $this->hasMany('App\Reserva');
    }
}
