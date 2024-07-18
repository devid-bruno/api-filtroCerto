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
        Plan::create([
            'name' => 'Portabilidade Ilimitada + WhatsApp Pré-paga',
            'portability_limit' => 'unlimited',
            'whatsapp_limit' => 0,
            'price_per_100k' => 100.00,
        ]);
    }
}
