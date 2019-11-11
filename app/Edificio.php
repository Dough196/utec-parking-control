<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Edificio extends Model
{
    protected $fillable = ['alias', 'nombre', 'num_parqueos', 'num_disponibles', 'num_ocupados', 'num_reservados'];

    public function reservas()
    {
        return $this->hasMany('App\Reserva')->where('num_slot', '<>', 0)->where('num_slot', '<>', null);
    }

    protected $appends = ['slots_disponibles'];

    public function getSlotsDisponiblesAttribute()
    {
        $slots = [];
        for ($i = 1; $i <= $this->num_parqueos; $i++) { 
            $slots[] = $i;
        }
        $slots_ocupados = $this->reservas()->whereNotNull('num_slot')->pluck('num_slot');
        return array_values(array_diff($slots, $slots_ocupados->toArray()));
    }
}
