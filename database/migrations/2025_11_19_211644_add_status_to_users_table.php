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
        Schema::table('users', function (Blueprint $table) {
        // status: 'pending' (Pendente), 'active' (Ativo), 'rejected' (Rejeitado)
        // Vamos deixar o default como 'active' POR ENQUANTO para não
        // bloquear seus testes, mas num sistema real seria 'pending'.
        $table->string('status')->default('active');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
