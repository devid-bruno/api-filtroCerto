<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UploadTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('upload_types')->insert([
            ['type_name' => 'Filtro Portabilidade'],
            ['type_name' => 'Whatsapp'],
            ['type_name' => 'Inclusao']
        ]); 
    }
}
