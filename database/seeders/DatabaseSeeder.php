<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        DB::table('users')->insert([
            'id' => 1,
            'username'=> 'Franz',
            'name'=> 'Franz',
            'email' => 'f.bie@email.com',
            'password' => Hash::make('12345'),
            'addedBy' => ' ',
            'status' => '1',
        ]);
    }
}
