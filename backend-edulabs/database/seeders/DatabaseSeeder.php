<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);

        // settings
        Setting::updateOrCreate(['key'=>'default_limit'], ['value' => 10485760]);
        Setting::updateOrCreate(['key'=>'forbidden_extensions'], ['value' => 'exe,bat,php,js,sh']);


        $this->call([
            SettingsTableSeeder::class,
            UserTableSeeder::class
        ]);
    }
}
