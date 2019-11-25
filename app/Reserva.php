<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = ['edificio_id', 'num_slot', 'estado', 'fecha'];

    public function user()
    {
        return $this->hasOne('App\User');
    }

    public function edificio()
    {
        return $this->belongsTo('App\Edificio');
    }
}
