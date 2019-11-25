<?php

namespace App\Console\Commands;

use App\Reserva;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FreeDayBookedParking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freeparking:daybooked';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Liberar todos los parqueos reservados vencidos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $reservas = Reserva::with('user.horarios')->get();
        foreach ($reservas as $reserva) {
            if (!empty($reserva->fecha)) {
                if(!empty($reserva->user)) {
                    foreach ($reserva->user->horarios as $horario) {
                        $hora_limite = Carbon::createFromFormat('Y-m-d H:s:i', $reserva->fecha . ' ' . $horario->hora_salida);
                        $diff = $hora_limite->diffInMinutes(now(), false);
                        if ($diff >= 0) {
                            $reserva->estado = 0;
                            $this->info('Entro');
                        }
                    }
                }
            }
        }
    }
}
