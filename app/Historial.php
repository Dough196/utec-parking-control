<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    protected $table = 'historial';

    protected $fillable = ['user_id', 'edificio_id', 'comentario', 'entrada', 'salida', 'fecha'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function edificio()
    {
        return $this->belongsTo('App\Edificio');
    }
}
