<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'cliente',
            'email' => 'cliente@mail.com',
            'password' => bcrypt('123456789'),
            'role_id' => 1,
            'payment_id' => 1
        ]);
    }
}
