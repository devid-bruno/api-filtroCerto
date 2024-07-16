<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create(['name' => 'Diamond', 'whatsapp_quota' => 100000, 'operator_quota' => 'unlimited']);
        Plan::create(['name' => 'Bronze', 'whatsapp_quota' => 50000, 'operator_quota' => 'unlimited']);
    }
}
