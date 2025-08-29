<?php

namespace App\Http\Controllers\Wpp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Wpp\WhatsAppService;
use App\Models\WaConfig;

class WebhookController extends Controller
{
    /**
     * Verifica o webhook (GET request)
     */
    public function verify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        // Token de verificação configurado no Meta for Developers
        $verifyToken = config('app.whatsapp_verify_token', 'your_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Webhook do WhatsApp verificado com sucesso');
            return response($challenge, 200);
        }

        Log::warning('Falha na verificação do webhook do WhatsApp', [
            'mode' => $mode,
            'token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Processa eventos do webhook (POST request)
     */
    public function handle(Request $request)
    {
        try {
            $signature = $request->header('X-Hub-Signature-256');
            $payload = $request->getContent();

            // Valida a assinatura do webhook
            if (!$this->validateSignature($payload, $signature)) {
                Log::warning('Assinatura inválida no webhook do WhatsApp');
                return response('Forbidden', 403);
            }

            $data = $request->json()->all();

            // Processa cada entrada do webhook
            foreach ($data['entry'] ?? [] as $entry) {
                $this->processEntry($entry);
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do WhatsApp', [
                'error' => $e->getMessage(),
                'payload' => $request->getContent(),
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Valida a assinatura do webhook
     */
    private function validateSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $appSecret = config('app.whatsapp_app_secret');
        if (!$appSecret) {
            Log::warning('WhatsApp App Secret não configurado');
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Processa uma entrada do webhook
     */
    private function processEntry(array $entry): void
    {
        $wabaId = $entry['id'] ?? null;
        if (!$wabaId) {
            return;
        }

        // Encontra a configuração correspondente
        $config = WaConfig::where('waba_id', $wabaId)->first();
        if (!$config) {
            Log::warning('Configuração do WhatsApp não encontrada', ['waba_id' => $wabaId]);
            return;
        }

        // Processa usando o serviço do WhatsApp
        $whatsappService = new WhatsAppService($config);
        $whatsappService->processWebhook(['entry' => [$entry]]);
    }
}
