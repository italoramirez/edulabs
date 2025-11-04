<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forbiddenExtensions = [
            'exe', 'sh', 'bat', 'php', 'js', 'html', 'sql'
        ];

        // 💾 Límite de almacenamiento por usuario (100 MB)
        $defaultLimit = 100 * 1024 * 1024; // bytes

        // Guardar o actualizar registros
        Setting::updateOrCreate(
            ['key' => 'forbidden_extensions'],
            ['value' => $forbiddenExtensions]
        );

        Setting::updateOrCreate(
            ['key' => 'default_limit'],
            ['value' => $defaultLimit]
        );
    }
}
