<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    use HasFactory;

    /**
     * A "lista de permissão" de campos que podem ser salvos.
     * ESTA É A CORREÇÃO DO SEU ERRO 500.
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
     * Relação: Um "Patient" PERTENCE A um "User" (o profissional que o criou).
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_professional_id');
    }
}
