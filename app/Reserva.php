<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = ['estado', 'fecha', 'comentario'];

    public function users()
    {
        return $this->belongsToMany('App\User', 'asignaciones')->withTimestamps();
    }

    public function edificios()
    {
        return $this->belongsToMany('App\Edificio', 'asignaciones')->withTimestamps();
    }

    public function horarios()
    {
        return $this->hasMany('App\Horario');
    }

    public function historial()
    {
        return $this->hasMany('App\Historial');
    }

    public function lastHistorial()
    {
        return $this->historial()->latest()->first();
    }
}
