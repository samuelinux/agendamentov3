<?php

namespace App\Services\Wpp;

use App\Models\WaConfig;
use App\Models\WaMessage;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private WaConfig $config;

    public function __construct(WaConfig $config)
    {
        $this->config = $config;
    }

    /** Normaliza para E.164 (Brasil) */
    private function formatPhoneNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);

        if (strlen($number) === 11) {            // DDD + número
            return '+55' . $number;
        }
        if (strlen($number) === 13 && str_starts_with($number, '55')) {
            return '+' . $number;                // já tem 55
        }
        if (str_starts_with($number, '+')) {
            return $number;                      // já está E.164
        }
        return $number;                          // fallback
    }

    /** Envia TEMPLATE aprovado e registra em wa_messages */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        array $parameters = [],
        ?int $agendamentoId = null,
        ?int $usuarioId = null,
        string $type = 'CONFIRM',
        string $language = 'pt_BR' // 👈 NOVO: idioma do template (default pt_BR)
    ): array {
        $to = $this->formatPhoneNumber($to);

        $template = [
            'name'     => $templateName,
            'language' => ['code' => $language], // 👈 usa idioma recebido
        ];

        if (!empty($parameters)) {
        $template['components'] = [[
            'type'       => 'body',
            'parameters' => array_map(fn($p) => ['type'=>'text','text'=>(string)$p], $parameters),
        ]];
    }

        $payload = [
        'messaging_product' => 'whatsapp',
        'to'                => $to,
        'type'              => 'template',
        'template'          => $template,
    ];

        $log = WaMessage::create([
            'empresa_id'     => $this->config->empresa_id,
            'agendamento_id' => $agendamentoId,
            'usuario_id'     => $usuarioId,
            'to_msisdn'      => $to,
            'type'           => $type,
            'template_name'  => $templateName,
            'payload'        => $payload,
            'status'         => 'QUEUED',
            'attempts'       => 1,
        ]);

        $res = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config->token,
            'Content-Type'  => 'application/json',
        ])->post("https://graph.facebook.com/v22.0/{$this->config->phone_number_id}/messages", $payload);

        if ($res->successful()) {
            $msgId = $res->json('messages.0.id');
            $log->update([
                'provider_message_id' => $msgId,
                'status'              => 'SENT',
                'sent_at'             => now(),
            ]);
            return ['success' => true, 'message_id' => $msgId, 'wa_message_id' => $log->id];
        }

        $log->update(['status' => 'FAILED', 'error' => json_encode($res->json())]);
        return ['success' => false, 'error' => $res->json()];
    }
}
