<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\AnalysisImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalysisController extends Controller
{
    /**
     * O endereço do seu serviço de IA em Python.
     * (Usamos 127.0.0.1 em vez de 'localhost' para evitar problemas de DNS)
     */
    private $pythonApiUrl = 'http://127.0.0.1:8000/predict_batch';

    /**
     * Recebe o formulário de "Nova Análise" do React, envia para a IA,
     * e salva tudo no banco de dados.
     */
   public function store(Request $request)
{
    \Log::info('=== INICIANDO ANÁLISE ===');
    \Log::info('User ID: ' . Auth::id());
    \Log::info('Dados recebidos (all):', $request->all());
    \Log::info('Arquivos recebidos:', array_keys($request->allFiles()));

    set_time_limit(300);

    // 1. Validação
    \Log::info('Validando dados...');

    try {
        $validatedData = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'exam_date' => 'required|date',
            'eye_examined' => 'required|string',
            'equipment' => 'nullable|string|max:255',
            'clinical_notes' => 'nullable|string',
            'files' => 'required|array|min:1',
            'files.*' => 'required|image|mimes:jpeg,png,jpg,tiff|max:10240'
        ]);

        \Log::info('✅ Validação OK!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('❌ ERRO DE VALIDAÇÃO:', $e->errors());
        return response()->json([
            'error' => 'Erro de validação',
            'details' => $e->errors()
        ], 422);
    }

    // 2. Prepara requisição para IA
\Log::info('Preparando requisição para IA...');
$httpRequest = Http::timeout(300)->asMultipart();
$localImagePaths = [];

foreach ($request->file('files') as $index => $image) {
    \Log::info("Processando imagem {$index}: " . $image->getClientOriginalName());

    // Anexa para a IA
    $httpRequest->attach(
        'files',
        file_get_contents($image->getRealPath()),
        $image->getClientOriginalName()
    );

    // Salva no Supabase Storage
    try {
        $fileName = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
        $path = 'exam_images/' . $fileName;

        \Log::info("Tentando salvar imagem {$index}...", [
            'path' => $path,
            'fileName' => $fileName,
            'disk' => config('filesystems.default'),
            'endpoint' => config('filesystems.disks.s3.endpoint'),
            'bucket' => config('filesystems.disks.s3.bucket'),
            'region' => config('filesystems.disks.s3.region')
        ]);

        // Tenta salvar
        $stored = Storage::disk('s3')->put($path, file_get_contents($image->getRealPath()));

        \Log::info("Resultado do Storage::put(): " . ($stored ? 'true' : 'false'));

        if ($stored) {
            $localImagePaths[] = $path;
            \Log::info("✅ Imagem {$index} salva em: {$path}");
        } else {
            \Log::error("❌ Storage::put retornou false para imagem {$index}");

            // Tenta obter mais detalhes do erro
            try {
                $testConnection = Storage::disk('s3')->exists('test.txt');
                \Log::info("Teste de conexão S3: " . ($testConnection ? 'OK' : 'Falhou'));
            } catch (\Exception $testError) {
                \Log::error("Erro ao testar conexão S3: " . $testError->getMessage());
            }

            throw new \Exception("Falha ao salvar imagem no storage");
        }

    } catch (\Exception $e) {
        \Log::error("❌ Exceção ao salvar imagem {$index}: " . $e->getMessage());
        \Log::error("Stack trace: " . $e->getTraceAsString());
        throw $e;
    }
}

    // 3. Chama a IA
    \Log::info('Enviando para IA em: ' . $this->pythonApiUrl);

    try {
        $response = $httpRequest->post($this->pythonApiUrl);

        \Log::info('Status da IA: ' . $response->status());

        if ($response->failed()) {
            \Log::error('IA falhou! Body: ' . $response->body());

            foreach ($localImagePaths as $path) {
                Storage::disk('s3')->delete($path);
            }
            return response()->json([
                'error' => 'Erro ao processar imagens na IA.',
                'details' => $response->body()
            ], 500);
        }

        $aiData = $response->json();
        \Log::info('✅ IA respondeu OK!');
        $aiData = $response->json();
        \Log::info('✅ IA respondeu OK!');
        \Log::info('Resposta completa da IA:', $aiData); // ← ADICIONE ESTA LINHA

        // 4. Salva análise
        \Log::info('Salvando análise no banco...');

    } catch (\Exception $e) {
        \Log::error('❌ Exceção ao chamar IA: ' . $e->getMessage());

        foreach ($localImagePaths as $path) {
            Storage::disk('s3')->delete($path);
        }
        return response()->json([
            'error' => 'Serviço de IA indisponível.',
            'details' => $e->getMessage()
        ], 503);
    }

    // 4. Salva análise
    \Log::info('Salvando análise no banco...');

    $analysis = Analysis::create([
        'patient_id' => $validatedData['patient_id'],
        'professional_id' => Auth::id(),
        'exam_date' => $validatedData['exam_date'],
        'eye_examined' => $validatedData['eye_examined'],
        'equipment' => $validatedData['equipment'] ?? null,
        'clinical_notes' => $validatedData['clinical_notes'] ?? null,
        'ai_summary_diagnosis' => $aiData['summary']['most_severe']['diagnosis'],
        'ai_summary_confidence' => $aiData['summary']['most_severe']['confidence'],
        'ai_summary_gravity' => $aiData['summary']['most_severe']['gravity_score'],
        'status' => 'pendente'
    ]);

    \Log::info('Análise criada com ID: ' . $analysis->id);

    // 5. Salva imagens
\Log::info('Salvando imagens individuais...');

if (isset($aiData['results']) && is_array($aiData['results'])) {
    foreach ($aiData['results'] as $index => $imageData) {
        \Log::info("Processando resultado da imagem {$index}:", $imageData);

        // Verifica se o resultado foi sucesso
        $isSuccess = isset($imageData['status']) && $imageData['status'] === 'success';

        // Se não tiver 'status', assume sucesso se tiver 'diagnosis'
        if (!isset($imageData['status']) && isset($imageData['diagnosis'])) {
            $isSuccess = true;
        }

        if ($isSuccess) {
            try {
                $analysis->images()->create([
                    'file_path' => $localImagePaths[$index] ?? null,
                    'file_name' => $imageData['metadata']['filename'] ?? $imageData['filename'] ?? "imagem_{$index}.png",
                    'ai_diagnosis' => $imageData['diagnosis'] ?? 'Não classificado',
                    'ai_confidence' => $imageData['confidence'] ?? 0,
                    'ai_gravity_score' => $imageData['gravity_score'] ?? 0,
                    'ai_probabilities' => json_encode($imageData['probabilities'] ?? []),
                ]);
                \Log::info("✅ Imagem {$index} salva no banco");
            } catch (\Exception $e) {
                \Log::error("❌ Erro ao salvar imagem {$index}: " . $e->getMessage());
            }
        } else {
            \Log::warning("⚠️ Imagem {$index} não processada com sucesso ou dados incompletos");
        }
    }
} else {
    \Log::error('❌ A IA não retornou o array "results" esperado!');
}

\Log::info('=== ✅ ANÁLISE CONCLUÍDA COM SUCESSO ===');

    return response()->json([
        'message' => 'Análise criada com sucesso!',
        'analysis_id' => $analysis->id,
    ], 201);
}
    public function index()
    {
        // 1. Pega o ID do profissional que está fazendo a requisição
        $professionalId = Auth::id();

        // 2. Busca no banco de dados (Supabase) todas as análises
        //    do profissional logado.

        // A MÁGICA ESTÁ AQUI:
        // 'with('patient')' diz ao Laravel: "Quando você buscar a análise,
        // por favor, já traga junto os dados do paciente (da tabela 'patients')
        // que está ligado a ela."
        $analyses = Analysis::with('patient')
                            ->where('professional_id', $professionalId)
                            ->latest() // Ordena das mais novas para as mais antigas
                            ->get();

        // 3. Retorna a lista de análises (com os dados do paciente "anexados")
        return response()->json($analyses);
    }

    public function show(Analysis $analysis)
    {
        // 1. O Laravel magicamente já encontrou a 'Analysis' usando o {id} da URL.

        // 2. Verificação de Segurança (Autorização):
        //    Este paciente pertence ao profissional que está logado?
        //    (Isso impede o Dr. João de ver os pacientes da Dra. Maria)
        if ($analysis->professional_id !== Auth::id()) {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        // 3. Carrega as "relações" que definimos nos Models.
        //    Buscamos a análise E "anexamos" os dados do paciente
        //    E a lista de todas as imagens (com seus resultados de IA).
        $analysis->load(['patient', 'images']);

        // 4. Retorna o JSON completo para o React
        return response()->json($analysis);
    }

    public function downloadLaudo(Analysis $analysis)
    {
        // 1. Autorização: O usuário logado é o profissional que
        //    criou a análise OU é um admin?
        $user = Auth::user();
        if ($user->id !== $analysis->professional_id && $user->role !== 'admin') {
            // Se não for, ele não pode baixar este laudo
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        // 2. Carrega as "relações" (Paciente e Profissional)
        //    para que possamos usar seus nomes no PDF
        $analysis->load(['patient', 'professional', 'images']);

        foreach ($analysis->images as $image) {
        try {
            $imageContent = Storage::disk('s3')->get($image->file_path);
            $image->image_base64 = 'data:image/png;base64,' . base64_encode($imageContent);
        } catch (\Exception $e) {
            $image->image_base64 = null;
        }
    }

        // 3. Carrega a "visão" (o template HTML que criamos)
        //    e passa os dados da análise para ele
        $pdf = Pdf::loadView('laudos.template', ['analysis' => $analysis]);

        // 4. Define um nome para o arquivo e força o download
        $fileName = 'laudo_' . $analysis->patient->nome . '_' . $analysis->id . '.pdf';

        return $pdf->download($fileName);
    }
}
