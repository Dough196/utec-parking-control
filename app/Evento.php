<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = ['edificio_id', 'horario_id', 'user_id', 'estado', 'cantidad', 'fecha', 'comentario'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function edificio()
    {
        return $this->belongsTo('App\Edificio');
    }
    
    public function horario()
    {
        return $this->hasOne('App\Horario');
    }
}
