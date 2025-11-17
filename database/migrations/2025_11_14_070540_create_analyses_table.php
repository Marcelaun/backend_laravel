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
        Schema::create('analyses', function (Blueprint $table) {
        $table->id();

        // --- As "Pontes" ---
        // Qual paciente foi analisado?
        $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
        // Qual profissional realizou a análise?
        $table->foreignId('professional_id')->constrained('users')->onDelete('cascade');

        // --- Dados do Exame (do formulário) ---
        $table->date('exam_date');
        $table->string('eye_examined'); // "Direito" ou "Esquerdo"
        $table->string('equipment')->nullable();
        $table->text('clinical_notes')->nullable();

        // --- O Veredito Final (do Profissional) ---
        $table->string('final_diagnosis')->nullable(); // O resultado que o médico validou
        $table->text('professional_conduct')->nullable(); // A recomendação final

        // --- O Sumário da IA (para a tela de histórico) ---
        $table->string('ai_summary_diagnosis'); // O resultado mais grave da IA
        $table->float('ai_summary_confidence');
        $table->integer('ai_summary_gravity');

        // Status do Laudo
        $table->enum('status', ['pendente', 'concluido'])->default('pendente');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
