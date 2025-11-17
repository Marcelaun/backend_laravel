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
       Schema::create('analysis_images', function (Blueprint $table) {
        $table->id();

        // A "ponte" que liga esta foto à sua "Pasta" de Análise
        $table->foreignId('analysis_id')->constrained('analyses')->onDelete('cascade');

        // O "endereço" da imagem no seu servidor
        // NÃO guardamos a imagem no banco, só o caminho para ela
        $table->string('file_path');
        $table->string('file_name');

        // O resultado da IA para ESTA imagem específica
        $table->string('ai_diagnosis');
        $table->float('ai_confidence');
        $table->integer('ai_gravity_score');
        $table->json('ai_probabilities'); // O array completo de probabilidades

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_images');
    }
};
