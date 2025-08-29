<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Servico;
use App\Models\Usuario;
use App\Models\Agendamento;
use App\Services\DisponibilidadeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendWhatsAppTemplateMessage;


class AgendamentoController extends Controller
{
    protected $disponibilidadeService;

    public function __construct(DisponibilidadeService $disponibilidadeService)
    {
        $this->disponibilidadeService = $disponibilidadeService;

    }

    /**
     * Mostra horários disponíveis para um serviço
     */
    public function mostrarHorarios(Empresa $empresa, Servico $servico)
    {
        // Verificar se o serviço pertence à empresa
        if ($servico->empresa_id !== $empresa->id) {
            abort(404);
        }

        // Verificar se o serviço está ativo
        if (!$servico->ativo) {
            return redirect()->route("empresa", $empresa->slug)
                ->with("error", "Este serviço não está disponível no momento.");
        }

        $quantidadeDeDias = (int) ($empresa->limite_dias_agenda ?? 1);
        $diasDisponiveis = $this->disponibilidadeService->obterProximosDiasDisponiveis($empresa, $servico, $quantidadeDeDias);

        $telefoneLogado = session('cliente_telefone');
        return view("agendamento.horarios", compact("empresa", "servico", "diasDisponiveis", "telefoneLogado"));
    }

    /**
     * Processa o agendamento
     */
    public function confirmarAgendamento(Request $request, Empresa $empresa, Servico $servico)
    {
        $request->validate([
            'data_hora_inicio' => 'required|date',
            'telefone_cliente' => 'required|string|max:20',
            'nome_cliente'     => 'nullable|string|max:255',
        ]);

        if ($servico->empresa_id !== $empresa->id || !$servico->ativo) abort(404);

        $inicio = Carbon::parse($request->data_hora_inicio);
        if ($inicio->isPast()) {
            return back()->withErrors(['data_hora_inicio' => 'Não é possível agendar no passado.']);
        }

        try {
            DB::beginTransaction();

            // cliente
            $fone = preg_replace('/\D/', '', $request->telefone_cliente);
            $cliente = Usuario::firstOrCreate(
                ['telefone' => $fone],
                ['nome' => $request->input('nome_cliente', 'Cliente'), 'tipo' => 'cliente']
            );

            // agendamento
            $agendamento = Agendamento::create([
                'empresa_id'      => $empresa->id,
                'servico_id'      => $servico->id,
                'usuario_id'      => $cliente->id,
                'data_hora_inicio'=> $inicio,
                'data_hora_fim'   => $inicio->copy()->addMinutes($servico->duracao_minutos),
                'status'          => 'confirmado',
            ]);

            DB::commit();

            // WA config obrigatória
            $waConfig = $empresa->waConfig;
            if ($waConfig) {
                // 1) confirmação imediata
                SendWhatsAppTemplateMessage::dispatch(
                    $waConfig->id,
                    $cliente->telefone,
                    'template_confirmacao',
                    [$cliente->nome, $agendamento->data_hora_inicio->format('d/m H:i')],
                    $agendamento->id,
                    $cliente->id,
                    'CONFIRM'
                );

                // 2) lembrete 60 min antes
                SendWhatsAppTemplateMessage::dispatch(
                    $waConfig->id,
                    $cliente->telefone,
                    'template_lembrete',
                    [$cliente->nome, $agendamento->data_hora_inicio->format('H:i')],
                    $agendamento->id,
                    $cliente->id,
                    'REMINDER'
                )->delay($agendamento->data_hora_inicio->subHour());
            }

            return view('agendamento.confirmacao', compact('agendamento', 'empresa', 'servico', 'cliente'));

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao processar agendamento. Tente novamente.']);
        }
    }

    /**
     * Verifica se o telefone já existe no banco de dados.
     */
    public function checkTelefone(Request $request, Empresa $empresa)
    {
        try {
            $request->validate([
                "telefone" => "required|string|max:20",
            ]);

            $telefone = preg_replace("/\\D/", "", $request->telefone); // Limpa a máscara

            // Log para debug
            \Log::info("Verificando telefone: " . $telefone);

            // Buscar usuário comparando apenas os números do telefone
            $usuario = Usuario::where("telefone", $telefone)->first();

            // Log para debug
            \Log::info("Usuário encontrado: " . ($usuario ? $usuario->nome : "Nenhum"));

            return response()->json([
                "success" => true,
                "exists" => (bool) $usuario,
                "nome" => $usuario ? $usuario->nome : null,
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro ao verificar telefone: " . $e->getMessage());

            return response()->json([
                "success" => false,
                "error" => "Erro interno do servidor",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancela um agendamento
     */
    public function cancelarAgendamento(Request $request, Empresa $empresa)
    {
        $request->validate([
            "agendamento_id" => "required|exists:agendamentos,id",
            "telefone" => "required|string"
        ]);

        $agendamento = Agendamento::with(["empresa", "servico", "usuario"])
            ->find($request->agendamento_id);

        // Verificar se o telefone confere
        if ($agendamento->usuario->telefone !== $request->telefone) {
            return back()->withErrors([
                "telefone" => "Telefone não confere com o agendamento."
            ]);
        }

        // Verificar se o agendamento pode ser cancelado (não pode estar no passado)
        if (Carbon::parse($agendamento->data_hora_inicio)->isPast()) {
            return back()->withErrors([
                "error" => "Não é possível cancelar agendamentos que já passaram."
            ]);
        }

        $agendamento->update(["status" => "cancelado"]);

        // Persistir o telefone do cliente na sessão para manter "logado"
        $telefoneLimpo = preg_replace("/\\D/", "", $request->telefone);
        session(['cliente_telefone' => $telefoneLimpo]);

        return back()->with("success", "Agendamento cancelado com sucesso!");
    }

    /**
     * Mostra formulário de cancelamento
     */
    public function mostrarCancelamento(Empresa $empresa)
    {
        return view("agendamento.cancelamento", 'empresa');
    }

    /**
     * Mostra formulário de login da área do cliente ou redireciona se já estiver logado
     */
    public function mostrarLoginCliente(Empresa $empresa)
    {
        // Verificar se o cliente já está logado na sessão
        $telefoneLogado = session('cliente_telefone');

        if ($telefoneLogado) {
            $cliente = Usuario::where("telefone", $telefoneLogado)->first();

            if ($cliente) {
                // Cliente está logado, redirecionar para a área do cliente
                return redirect()->route('cliente.area.logado', $empresa);
            } else {
                // Cliente não existe mais, limpar a sessão
                session()->forget('cliente_telefone');
            }
        }

        // Cliente não está logado, mostrar formulário de login
        return view('cliente.login', [
        'empresa' => $empresa,
    ]);
    }

    /**
     * Processa o login e exibe a área do cliente
     */
    public function areaCliente(Request $request, Empresa $empresa)
    {
        $request->validate([
            "telefone" => "required|string|max:20",
        ]);

        $telefoneLimpo = preg_replace("/\\D/", "", $request->telefone);

        $cliente = Usuario::where("telefone", $telefoneLimpo)->first();

        if (!$cliente) {
            return back()->withErrors([
                "telefone" => "Nenhum agendamento encontrado para este telefone."
            ])->withInput();
        }

        // Persistir o telefone do cliente na sessão para manter "logado"
        session(['cliente_telefone' => $telefoneLimpo]);

        // LIMPA antes de preencher
        $agendamentos = collect();

        // Buscar todos os agendamentos do cliente, ordenados por data
        $agendamentos = Agendamento::with(["empresa", "servico"])
            ->where("usuario_id", $cliente->id)
            ->where('empresa_id', $empresa->id)
            ->orderBy("data_hora_inicio", "desc")
            ->get();

        return view("cliente.area", compact("cliente", "agendamentos", "empresa"));
    }

    /**
     * Verifica se há cliente logado na sessão e exibe a área do cliente
     */
    public function areaClienteLogado(Empresa $empresa)
    {
        $telefoneLogado = session('cliente_telefone');

        if (!$telefoneLogado) {
            return redirect()->route('cliente.login', $empresa);
        }

        $cliente = Usuario::where("telefone", $telefoneLogado)->first();

        if (!$cliente) {
            // Se o cliente não existe mais, limpar a sessão
            session()->forget('cliente_telefone');
            return redirect()->route('cliente.login', $empresa);
        }

        // Buscar todos os agendamentos do cliente, ordenados por data
        $agendamentos = Agendamento::with(["empresa", "servico"])
            ->where("usuario_id", $cliente->id)
            ->where('empresa_id', $empresa->id)
            ->orderBy("data_hora_inicio", "desc")
            ->get();

        return view("cliente.area", compact("cliente", "agendamentos", 'empresa'));
    }

    /**
     * Faz logout do cliente (limpa a sessão)
     */
    public function logoutCliente(Empresa $empresa)
    {
        session()->forget('cliente_telefone');
        return redirect()->route('cliente.login', $empresa)->with('success', 'Logout realizado com sucesso!');
    }
}
