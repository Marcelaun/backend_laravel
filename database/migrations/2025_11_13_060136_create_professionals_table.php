<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();

            // Esta é a "ponte" que liga esta tabela à tabela 'users'
            $table->foreignId('user_id')
                  ->constrained('users') // Diz que 'user_id' se refere a 'id' na tabela 'users'
                  ->onDelete('cascade'); // Se o 'user' for deletado, delete este perfil também

            // Seus campos do RF01 que faltavam
            $table->string('cpf')->unique()->nullable();
            $table->string('registro_profissional')->nullable();
            $table->string('telefone')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
