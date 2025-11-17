<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== TESTE DE CONEXÃO SUPABASE S3 ===\n\n";

// 1. Mostra as configurações
echo "1. Configurações carregadas:\n";
echo "   Disk: " . config('filesystems.default') . "\n";
echo "   Endpoint: " . config('filesystems.disks.s3.endpoint') . "\n";
echo "   Bucket: " . config('filesystems.disks.s3.bucket') . "\n";
echo "   Region: " . config('filesystems.disks.s3.region') . "\n";
echo "   Key: " . substr(config('filesystems.disks.s3.key'), 0, 10) . "..." . "\n";
echo "   Secret: " . (config('filesystems.disks.s3.secret') ? 'Configurado' : 'NÃO configurado') . "\n\n";

// 2. Testa a conexão
echo "2. Testando upload de arquivo...\n";

try {
    $content = 'Hello Supabase! ' . now();
    $result = Storage::disk('s3')->put('test/hello.txt', $content);

    if ($result) {
        echo "   ✅ SUCESSO! Arquivo salvo!\n";
        echo "   Path: test/hello.txt\n";

        // Tenta gerar URL
        try {
            $url = Storage::disk('s3')->url('test/hello.txt');
            echo "   URL: {$url}\n";
        } catch (\Exception $e) {
            echo "   ⚠️ Não conseguiu gerar URL: " . $e->getMessage() . "\n";
        }

        // Tenta ler
        try {
            $readContent = Storage::disk('s3')->get('test/hello.txt');
            echo "   Conteúdo lido: {$readContent}\n";
        } catch (\Exception $e) {
            echo "   ⚠️ Não conseguiu ler: " . $e->getMessage() . "\n";
        }

        // Limpa
        Storage::disk('s3')->delete('test/hello.txt');
        echo "   Arquivo de teste removido.\n";

    } else {
        echo "   ❌ FALHOU! Storage::put() retornou false\n";
        echo "   Isso geralmente significa:\n";
        echo "   - Credenciais S3 incorretas\n";
        echo "   - Bucket não existe\n";
        echo "   - Permissões insuficientes\n";
    }

} catch (\Aws\S3\Exception\S3Exception $e) {
    echo "   ❌ ERRO S3:\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getAwsErrorCode() . "\n";
    echo "   Status HTTP: " . $e->getStatusCode() . "\n\n";
    echo "   Possíveis causas:\n";

    if (str_contains($e->getMessage(), 'InvalidAccessKeyId')) {
        echo "   - Access Key ID está incorreta\n";
    } elseif (str_contains($e->getMessage(), 'SignatureDoesNotMatch')) {
        echo "   - Secret Access Key está incorreta\n";
    } elseif (str_contains($e->getMessage(), 'NoSuchBucket')) {
        echo "   - O bucket 'exam_images' não existe no Supabase\n";
    } else {
        echo "   - Erro desconhecido. Verifique as credenciais.\n";
    }

} catch (\Exception $e) {
    echo "   ❌ ERRO GERAL:\n";
    echo "   Tipo: " . get_class($e) . "\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
