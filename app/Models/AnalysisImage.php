<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AnalysisImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Adiciona o campo image_url automaticamente
    protected $appends = ['image_url'];

    // Converte o JSON de probabilidades em array
    protected $casts = [
        'ai_probabilities' => 'array',
    ];

    /**
     * Retorna a URL pública da imagem no Supabase
     */
    public function getImageUrlAttribute()
    {
        if (!$this->file_path || $this->file_path === '0') {
            return null;
        }

        try {
            return Storage::disk('s3')->url($this->file_path);
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar URL da imagem: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Relação: Uma Imagem PERTENCE A uma Análise.
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
