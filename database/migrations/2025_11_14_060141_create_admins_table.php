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
       Schema::create('admins', function (Blueprint $table) {
            $table->id();

            // A "ponte" que liga esta tabela à tabela 'users'
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // Se o 'user' for deletado, delete este perfil

            // Um campo para o tipo de admin (ex: "superadmin", "manager")
            $table->string('permission_level')->default('admin');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
