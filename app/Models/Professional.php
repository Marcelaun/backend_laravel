<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Professional extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'user_id',
        'cpf',
        'registro_profissional',
        'telefone',
    ];

    /**
     * Define a relação: Um "Professional" (perfil) PERTENCE A um "User" (login).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
