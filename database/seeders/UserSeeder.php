<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        //assign admin role
        $admin->assignRole('admin');

        $user = User::create([
            'name' => 'Waiter',
            'email' => 'waiter@test.com',
            'password' => 'password',
        ]);

        //assign user role
        $user->assignRole('waiter');
    }
}
