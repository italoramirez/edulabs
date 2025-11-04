<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $group = Group::firstOrCreate(
            ['name' => 'General'],
            ['storage_limit' => 52428800] // 50 MB
        );

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'storage_limit' => 104857600, // 100 MB
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Usuario estándar',
                'password' => Hash::make('password'),
                'role' => 'user',
                'group_id' => $group->id,
                'storage_limit' => 52428800, // 50 MB
            ]
        );

        $this->command->info('✅ Usuarios de ejemplo creados: admin@example.com / user@example.com (password: "password")');
    }
}
