<?php

namespace GemaDigital\Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $i = -1;
        while ($this->command->confirm('Add '.($i++ ? 'an' : 'another').' admin user?')) {
            $name = $this->command->ask('Name');
            $mail = $this->command->ask("{$name}'s email");
            $pass = $this->command->secret("{$name}'s password");

            User::insert([
                'name' => $name,
                'email' => $mail,
                'password' => bcrypt($pass),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_admin' => true,
            ]);
        }
    }
}
