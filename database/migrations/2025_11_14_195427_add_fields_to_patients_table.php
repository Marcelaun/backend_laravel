<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // ADD APENAS CAMPOS QUE NÃO EXISTEM
            $table->foreignId('created_by_professional_id')->nullable()->constrained('users');
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable()->unique();
            $table->date('birth_date')->nullable();
            $table->string('sexo')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('tipo_diabetes')->nullable();
            $table->string('usa_insulina')->nullable();
            $table->string('diagnosis_time')->nullable();
            $table->text('current_medication')->nullable();
            $table->text('comorbidities')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'created_by_professional_id',
                'nome',
                'cpf',
                'birth_date',
                'sexo',
                'telefone',
                'email',
                'tipo_diabetes',
                'usa_insulina',
                'diagnosis_time',
                'current_medication',
                'comorbidities',
            ]);
        });
    }
};
