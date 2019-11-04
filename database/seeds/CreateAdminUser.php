<?php

use App\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate([
            'nombres' => 'Super',
            'apellidos' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'estado' => 1,
            'rol_id' => 1
        ]);
        $this->command->info('User admin created');
    }
}
