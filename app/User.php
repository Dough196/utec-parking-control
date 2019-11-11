<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'nombres', 'apellidos', 'num_placa', 'reserva_id', 'rol_id', 'password', 'estado'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function generateToken()
    {
        $this->api_token = Str::random(60);
        $this->save();

        return $this->api_token;
    }

    public function rol()
    {
        return $this->belongsTo('App\Rol', 'rol_id', 'id');
    }

    public function reserva()
    {
        return $this->belongsTo('App\Reserva', 'reserva_id', 'id')->with('edificio');
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
