<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laudo de Triagem</title>
    <style>
        /* Estilos para o PDF (simplificado) */
        body { font-family: 'Helvetica', sans-serif; line-height: 1.6; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #33b9b9; padding-bottom: 10px; }
        .header h1 { color: #33b9b9; margin: 0; }
        .section { margin-bottom: 20px; border: 1px solid #eee; border-radius: 8px; padding: 15px; }
        .section h2 { font-size: 1.2rem; color: #33b9b9; margin-top: 0; border-bottom: 1px solid #f0f0f0; padding-bottom: 5px;}
        .info-grid { display: block; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: bold; color: #555; }
        .info-value { font-weight: normal; }
        .diagnosis-box { background-color: #FEF9F0; border: 1px solid #F39C12; padding: 15px; border-radius: 8px; text-align: center; }
        .diagnosis-title { font-size: 1.3rem; font-weight: bold; color: #F39C12; margin: 0; }
        .recommendation { font-size: 1rem; line-height: 1.7; }
        .footer-warning { font-size: 0.8rem; color: #777; text-align: center; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('assets/Logo.png') }}" alt="Logo VisusAI" style="height: 60px; margin-bottom: 10px;">

        <h1>Plataforma VisusAI</h1>
        <p>Laudo de Triagem de Retinopatia Diabética</p>
    </div>

    <div class="section">
        <h2>Dados do Paciente</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Paciente:</span>
                <span class="info-value">{{ $analysis->patient->nome }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">CPF:</span>
                <span class="info-value">{{ $analysis->patient->cpf ?? 'Não informado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Data de Nasc.:</span>
                <span class="info-value">
                    {{ $analysis->patient->birth_date ? \Carbon\Carbon::parse($analysis->patient->birth_date)->format('d/m/Y') : 'Não informado' }}
                </span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Dados da Análise</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Data do Exame:</span>
                <span class="info-value">{{ $analysis->exam_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Olho Examinado:</span>
                <span class="info-value">{{ $analysis->eye_examined }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Profissional:</span>
                <span class="info-value">{{ $analysis->professional->name ?? 'Não informado' }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Resultado da Triagem</h2>
        <div class="diagnosis-box">
            <span class="info-label" style="font-size: 1rem;">Resultado Final</span>
            <h3 class="diagnosis-title">{{ $analysis->final_diagnosis ?? $analysis->ai_summary_diagnosis }}</h3>
        </div>
    </div>

  <div class="section">
    <h2>Análise Detalhada por Imagem</h2>
</div>

@foreach($analysis->images as $index => $image)
    <div style="page-break-inside: avoid; margin-bottom: 25px; border: 1px solid #ddd; padding: 12px; border-radius: 6px;">

        <h3 style="color: #33b9b9; font-size: 1rem; margin: 0 0 10px 0;">
            Imagem {{ $index + 1 }}: {{ $image->file_name }}
        </h3>

        <div style="text-align: center; margin-bottom: 12px;">
            @if(isset($image->image_base64) && $image->image_base64)
                <img src="{{ $image->image_base64 }}"
                     alt="Imagem {{ $index + 1 }}"
                     style="max-width: 100%; max-height: 220px; height: auto; border: 2px solid #33b9b9; border-radius: 6px;">
            @else
                <div style="background: #f0f0f0; padding: 40px; border-radius: 6px; font-size: 0.9rem; color: #999;">
                    Imagem não disponível
                </div>
            @endif
        </div>

        <div>
            <div style="background: #FEF9F0; border-left: 4px solid #F39C12; padding: 10px; margin-bottom: 12px; border-radius: 4px;">
                <div style="font-size: 0.75rem; color: #888; margin-bottom: 3px; text-transform: uppercase;">
                    Diagnóstico:
                </div>
                <div style="font-size: 1.1rem; font-weight: bold; color: #F39C12;">
                    {{ $image->ai_diagnosis }}
                </div>
            </div>

            <table style="width: 100%; margin-bottom: 12px;">
                <tr>
                    <td style="width: 50%; padding-right: 8px;">
                        <div style="background: #E8F8F5; border-left: 3px solid #27ae60; padding: 8px; border-radius: 4px;">
                            <div style="font-size: 0.75rem; color: #555; margin-bottom: 2px;">Confiança:</div>
                            <div style="font-size: 1.1rem; font-weight: bold; color: #27ae60;">
                                {{ $image->ai_confidence }}%
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 8px;">
                        <div style="background: #FEF5E7; border-left: 3px solid #E67E22; padding: 8px; border-radius: 4px;">
                            <div style="font-size: 0.75rem; color: #555; margin-bottom: 2px;">Gravidade:</div>
                            <div style="font-size: 1.1rem; font-weight: bold; color: #E67E22;">
                                {{ $image->ai_gravity_score }} / 4
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="background: #FAFAFA; padding: 10px; border-radius: 6px; border: 1px solid #E0E0E0;">
                <div style="font-size: 0.85rem; font-weight: bold; margin-bottom: 8px; color: #333;">
                    📊 Distribuição de Probabilidades
                </div>

                @php
                    $probabilities = is_array($image->ai_probabilities)
                        ? $image->ai_probabilities
                        : json_decode($image->ai_probabilities, true);
                @endphp

                @if($probabilities && is_array($probabilities))
                    @foreach($probabilities as $prob)
                    <div style="margin-bottom: 6px;">
                        <div style="display: table; width: 100%; margin-bottom: 2px;">
                            <div style="display: table-cell; width: 60%; font-size: 0.75rem; color: #555;">
                                {{ $prob['label'] }}
                            </div>
                            <div style="display: table-cell; width: 40%; text-align: right; font-size: 0.75rem; color: #333; font-weight: bold;">
                                {{ number_format($prob['value'], 2) }}%
                            </div>
                        </div>
                        <div style="background: #E0E0E0; height: 12px; border-radius: 6px; overflow: hidden; position: relative;">
                            <div style="background: #33b9b9; height: 100%; width: {{ min($prob['value'], 100) }}%; position: absolute; top: 0; left: 0;"></div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

        </div>
    </div>
@endforeach

    <div class="section">
        <h2>Recomendação Médica</h2>
        <p class="recommendation">
            {{ $analysis->professional_conduct ?? 'Aguardando validação profissional.' }}
        </p>
    </div>

    <div class="footer-warning">
        <p>Este é um resultado de triagem e apoio. Os resultados gerados pela Inteligência Artificial não constituem um diagnóstico médico final e não substituem a avaliação de um profissional de saúde qualificado.</p>
    </div>

</body>
</html>
