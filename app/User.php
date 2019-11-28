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
        'email', 'carnet', 'nombres', 'apellidos', 'num_placa', 'reserva_id', 'rol_id', 'password', 'estado'
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

    public function reservas()
    {
        return $this->belongsToMany('App\Reserva', 'asignaciones')->withTimestamps();
    }

    public function edificios()
    {
        return $this->belongsToMany('App\Edificio', 'asignaciones')->withTimestamps();
    }
}
