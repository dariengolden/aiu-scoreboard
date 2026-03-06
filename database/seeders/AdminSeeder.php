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
            ['email' => 'chronos@admin.local'],
            [
                'name' => 'chronos',
                'password' => Hash::make('$3Vnthd#2544'),
                'role' => 'admin',
            ]
        );
    }
}
