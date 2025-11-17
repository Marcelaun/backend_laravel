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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_professional_id')->constrained('users');
            $table->string('nome'); // <-- A COLUNA QUE FALTAVA
            $table->string('cpf')->nullable()->unique();
            $table->date('birth_date');
            $table->string('sexo');
            $table->string('telefone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('tipo_diabetes');
            $table->string('usa_insulina');
            $table->string('diagnosis_time')->nullable();
            $table->text('current_medication')->nullable();
            $table->text('comorbidities')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
