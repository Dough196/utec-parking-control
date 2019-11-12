<?php

use App\Edificio;
use Illuminate\Database\Seeder;

class CreateEdificiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Edificio::firstOrCreate([
            'alias' => 'BJ',
            'nombre' => 'Benito Juarez',
            'num_parqueos' => 60,
            'num_disponible' => 60,
            'num_ocupado' => 0,
            'num_reservados' => 0
        ]);
        $this->command->info('Edificio BJ created');
        Edificio::firstOrCreate([
            'alias' => 'JL',
            'nombre' => 'Jorge Luis Borges',
            'num_parqueos' => 30,
            'num_disponible' => 30,
            'num_ocupado' => 0,
            'num_reservados' => 0
        ]);
        $this->command->info('Edificio JL created');
        Edificio::firstOrCreate([
            'alias' => 'SB',
            'nombre' => 'Simon Bolivar',
            'num_parqueos' => 70,
            'num_disponible' => 70,
            'num_ocupado' => 0,
            'num_reservados' => 0
        ]);
        $this->command->info('Edificio SB created');
        Edificio::firstOrCreate([
            'alias' => 'PD',
            'nombre' => 'Polideportivo',
            'num_parqueos' => 55,
            'num_disponible' => 55,
            'num_ocupado' => 0,
            'num_reservados' => 0
        ]);
        $this->command->info('Edificio PD created');
    }
}
