<?php

namespace App\Jobs;

use App\Models\WaConfig;
use App\Services\Wpp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppTemplateMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int|string $waConfigId;
    private string $to;
    private string $templateName;
    private array $parameters;
    private ?int $agendamentoId;
    private ?int $usuarioId;
    private string $type;

    private string $language; // 👈 NOVO

    public function __construct(
        $waConfigId,
        string $to,
        string $templateName,
        array $parameters = [],
        ?int $agendamentoId = null,
        ?int $usuarioId = null,
        string $type = 'CONFIRM',
        string $language = 'pt_BR' // 👈 default mantém seu fluxo atual
    ) {
        $this->waConfigId    = $waConfigId;
        $this->to            = $to;
        $this->templateName  = $templateName;
        $this->parameters    = $parameters;
        $this->agendamentoId = $agendamentoId;
        $this->usuarioId     = $usuarioId;
        $this->type          = $type;
        $this->language      = $language; // 👈 guarda o idioma
    }

    public function handle(): void
    {
        $config = WaConfig::find($this->waConfigId);
        if (!$config) return;

        (new WhatsAppService($config))->sendTemplateMessage(
            $this->to,
            $this->templateName,
            $this->parameters,
            $this->agendamentoId,
            $this->usuarioId,
            $this->type,
            $this->language // 👈 passa o idioma
        );
    }
}
