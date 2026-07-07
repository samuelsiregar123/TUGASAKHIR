<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@spbescan.go.id'],
            [
                'name'     => 'Admin SPBE-SCAN',
                'password' => Hash::make('Admin1234!'),
                'role'     => 'admin',
            ]
        );
    }
}
