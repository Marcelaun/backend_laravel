<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Importe o HasMany

class Patient extends Model
{
    use HasFactory;

    /**
     * A "lista de permissão" de campos que podem ser salvos.
     */
    protected $fillable = [
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
    ];

    /**
     * Conversão automática de tipos.
     * Isso garante que 'birth_date' seja tratado como data, não texto.
     */
    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Relação: Um "Patient" PERTENCE A um "User" (o profissional que o criou).
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_professional_id');
    }

    /**
     * Relação: Um "Patient" TEM MUITAS "Analyses" (Histórico completo).
     * (Vamos usar isso na tela de Detalhes do Paciente)
     */
    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    /**
     * Relação: Pega apenas a ÚLTIMA análise (Para a lista rápida).
     */
    public function latestAnalysis()
    {
        return $this->hasOne(Analysis::class)->latestOfMany();
    }
}
