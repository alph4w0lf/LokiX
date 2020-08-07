<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        // Insert Default Admin account
        DB::table('users')->insert([
            'name' => 'Default LokiX Account',
            'email' => 'admin@lokix.local',
            'password' => Hash::make('password')
        ]);

        // Insert Loki status
        DB::table('lokis')->insert([
            'ready' => true,
            'updating' => false,
            'appended' => false,
            'last_update' => now()
        ]);
    
    }
}
