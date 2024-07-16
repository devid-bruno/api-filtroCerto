<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nome da restrição de chave estrangeira
        $foreign_key_constraint_name = 'users_payment_id_foreign';

        // Remover a restrição de chave estrangeira
        if (config('database.default') === 'mysql') {
            // Para MySQL
            DB::statement("SET foreign_key_checks=0");
            DB::statement("ALTER TABLE users DROP FOREIGN KEY $foreign_key_constraint_name");
            DB::statement("SET foreign_key_checks=1");
        } else {
            // Se estiver usando outro banco de dados, ajuste de acordo
            // Consulte a documentação do seu banco de dados para a sintaxe correta
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não é necessário fazer nada na reversão neste caso
    }
};
