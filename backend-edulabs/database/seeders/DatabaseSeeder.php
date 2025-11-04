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

        // groups
        $group = Group::create(['name' => 'Developers']);

        // users
        User::create([ 'name' => 'Admin', 'email' => 'admin@demo.com', 'password' => bcrypt('password'), 'role' => 'admin' ]);
        User::create([ 'name' => 'User', 'email' => 'user@demo.com', 'password' => bcrypt('password'), 'role' => 'user', 'group_id' => $group->id ]);

        $this->call([
            SettingsTableSeeder::class,
        ]);
    }
}
