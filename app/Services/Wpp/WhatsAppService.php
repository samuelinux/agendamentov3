<?php

namespace App\Services\Wpp;

use App\Models\WaConfig;
use App\Models\WaMessage;
use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $config;

    public function __construct(WaConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Valida se um número de telefone tem WhatsApp
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config->token,
                'Content-Type' => 'application/json',
            ])->post("https://graph.facebook.com/v20.0/{$this->config->phone_number_id}/contacts", [
                'blocking' => 'wait',
                'contacts' => [$phoneNumber],
                'force_check' => true,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $contacts = $data['contacts'] ?? [];
                
                if (!empty($contacts)) {
                    $contact = $contacts[0];
                    return isset($contact['wa_id']) && $contact['status'] === 'valid';
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao validar número do WhatsApp', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Envia uma mensagem usando template
     */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        array $parameters = [],
        ?int $agendamentoId = null,
        ?int $usuarioId = null,
        string $type = 'CONFIRM'
    ): array {
        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'pt_BR'
                    ]
                ]
            ];

            if (!empty($parameters)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => array_map(function ($param) {
                            return ['type' => 'text', 'text' => $param];
                        }, $parameters)
                    ]
                ];
            }

            // Registra a mensagem como QUEUED
            $waMessage = WaMessage::create([
                'empresa_id' => $this->config->empresa_id,
                'agendamento_id' => $agendamentoId,
                'usuario_id' => $usuarioId,
                'to_msisdn' => $to,
                'type' => $type,
                'template_name' => $templateName,
                'payload' => $payload,
                'status' => 'QUEUED',
                'attempts' => 1,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config->token,
                'Content-Type' => 'application/json',
            ])->post("https://graph.facebook.com/v20.0/{$this->config->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;

                $waMessage->update([
                    'provider_message_id' => $messageId,
                    'status' => 'SENT',
                    'sent_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'wa_message_id' => $waMessage->id,
                ];
            } else {
                $error = $response->json();
                $waMessage->update([
                    'status' => 'FAILED',
                    'error' => json_encode($error),
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem do WhatsApp', [
                'to' => $to,
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);

            if (isset($waMessage)) {
                $waMessage->update([
                    'status' => 'FAILED',
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Processa webhook do WhatsApp
     */
    public function processWebhook(array $data): bool
    {
        try {
            $entry = $data['entry'][0] ?? null;
            if (!$entry) {
                return false;
            }

            $changes = $entry['changes'][0] ?? null;
            if (!$changes || $changes['field'] !== 'messages') {
                return false;
            }

            $value = $changes['value'] ?? null;
            if (!$value) {
                return false;
            }

            // Processa status de mensagens
            if (isset($value['statuses'])) {
                foreach ($value['statuses'] as $status) {
                    $this->updateMessageStatus($status);
                }
            }

            // Processa mensagens recebidas (para opt-out)
            if (isset($value['messages'])) {
                foreach ($value['messages'] as $message) {
                    $this->processIncomingMessage($message);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do WhatsApp', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Atualiza o status de uma mensagem
     */
    private function updateMessageStatus(array $status): void
    {
        $messageId = $status['id'] ?? null;
        $statusValue = $status['status'] ?? null;

        if (!$messageId || !$statusValue) {
            return;
        }

        $waMessage = WaMessage::where('provider_message_id', $messageId)->first();
        if (!$waMessage) {
            return;
        }

        $statusMap = [
            'sent' => 'SENT',
            'delivered' => 'DELIVERED',
            'read' => 'READ',
            'failed' => 'FAILED',
        ];

        $newStatus = $statusMap[$statusValue] ?? null;
        if ($newStatus) {
            $waMessage->update(['status' => $newStatus]);
        }
    }

    /**
     * Processa mensagens recebidas (para detectar opt-out)
     */
    private function processIncomingMessage(array $message): void
    {
        $from = $message['from'] ?? null;
        $text = $message['text']['body'] ?? '';

        if (!$from) {
            return;
        }

        // Palavras-chave para opt-out
        $optOutKeywords = ['STOP', 'PARAR', 'SAIR', 'CANCELAR'];
        $textUpper = strtoupper(trim($text));

        if (in_array($textUpper, $optOutKeywords)) {
            // Atualiza o opt-in do usuário para false
            Usuario::where('telefone', $from)->update([
                'whatsapp_opt_in' => false,
            ]);

            Log::info('Usuário fez opt-out do WhatsApp', [
                'phone' => $from,
                'message' => $text,
            ]);
        }
    }
}

