<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin'),
            'role' => 1,
        ]);

        User::create([
                'name' => 'dia',
                'email' => 'dia@gmail.com',
                'password' => bcrypt('password'),
                'role' => 2,
            ]
        );
    }
}
