<?php

use App\Rol;
use Illuminate\Database\Seeder;

class CreateRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Rol::firstOrCreate([
            'nombre' => 'Administrador'
        ]);
        $this->command->info('Role Administrador created');
        Rol::firstOrCreate([
            'nombre' => 'Personal Administrativo'
        ]);
        $this->command->info('Role Docente created');
        Rol::firstOrCreate([
            'nombre' => 'Docente'
        ]);
        $this->command->info('Role Docente created');
        Rol::firstOrCreate([
            'nombre' => 'Alumno'
        ]);
        $this->command->info('Role Alumno created');
        Rol::firstOrCreate([
            'nombre' => 'Vigilante'
        ]);
        $this->command->info('Role Vigilante created');
    }
}
