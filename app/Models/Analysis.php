<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analysis extends Model
{
    use HasFactory;

    // Proteção contra Mass Assignment
    protected $guarded = [];

    // Converte os JSONs do Supabase para arrays PHP
    protected $casts = [
        'exam_date' => 'date',
    ];

    /**
     * Relação: Uma Análise (Pasta) PERTENCE A um Paciente.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relação: Uma Análise (Pasta) PERTENCE A um Profissional (Usuário).
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professional_id');
    }

    /**
     * Relação: Uma Análise (Pasta) TEM MUITAS Imagens.
     */
    public function images(): HasMany
    {
        return $this->hasMany(AnalysisImage::class);
    }
}
