<?php

use App\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate([
            'email' => 'admin@admin.com',
            'num_placa' => '1234-5678',
            'nombres' => 'Super',
            'apellidos' => 'Admin',
            'password' => Hash::make('password'),
            'estado' => 1,
            'rol_id' => 1,
            'api_token' => Str::random(80)
        ]);
        $this->command->info('User admin created');
    }
}
