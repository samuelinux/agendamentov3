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

        $diasDisponiveis = $this->disponibilidadeService->obterProximosDiasDisponiveis($empresa, $servico);

        $telefoneLogado = session('cliente_telefone');
        return view("agendamento.horarios", compact("empresa", "servico", "diasDisponiveis", "telefoneLogado"));
    }

    /**
     * Processa o agendamento
     */
    public function confirmarAgendamento(Request $request, Empresa $empresa, Servico $servico)
    {
        $rules = [
            "data_hora_inicio" => "required|date",
            "telefone_cliente" => "required|string|max:20",
        ];

        // Se o nome_cliente não for fornecido, significa que o usuário já existe
        if (!$request->has("nome_cliente") || empty($request->nome_cliente)) {
            // Se o usuário já existe, não precisamos validar o nome
        } else {
            // Se for um novo usuário, o nome é obrigatório
            $rules["nome_cliente"] = "required|string|max:255";
        }

        $request->validate($rules);

        // Verificar se o serviço pertence à empresa e está ativo
        if ($servico->empresa_id !== $empresa->id || !$servico->ativo) {
            abort(404);
        }

        $dataHoraInicio = Carbon::parse($request->data_hora_inicio);
        
        // Verificar se o horário de início não é no passado
        if ($dataHoraInicio->isPast()) {
            return back()->withErrors([
                "data_hora_inicio" => "Não é possível agendar para um horário que já passou."
            ]);
        }

        $dataHoraFim = $dataHoraInicio->copy()->addMinutes($servico->duracao_minutos);

        // Verificar se o horário ainda está disponível
        $horariosDisponiveis = $this->disponibilidadeService->gerarHorariosDisponiveis(
            $empresa, 
            $servico, 
            $dataHoraInicio
        );

        $horarioDisponivel = $horariosDisponiveis->first(function ($horario) use ($request) {
            return $horario["data_hora_inicio"] === $request->data_hora_inicio;
        });

        if (!$horarioDisponivel) {
            return back()->withErrors([
                "data_hora_inicio" => "Este horário não está mais disponível. Por favor, escolha outro horário."
            ]);
        }

        try {
            DB::beginTransaction();

            // Buscar ou criar cliente
            $telefoneLimpo = preg_replace("/\\D/", "", $request->telefone_cliente); // Limpa a máscara
            $cliente = Usuario::where("telefone", $telefoneLimpo)->first();
            
            if (!$cliente) {
                // Criar novo usuário se não existir
                $cliente = Usuario::create([
                    "nome" => $request->nome_cliente,
                    "telefone" => $telefoneLimpo,
                    "tipo" => "cliente"
                ]);
            } else {
                // Se o nome foi fornecido e é diferente, atualizar (caso o usuário tenha digitado um nome diferente)
                if ($request->has("nome_cliente") && !empty($request->nome_cliente) && $cliente->nome !== $request->nome_cliente) {
                    $cliente->update(["nome" => $request->nome_cliente]);
                }
            }

            // Criar agendamento
            $agendamento = Agendamento::create([
                "empresa_id" => $empresa->id,
                "servico_id" => $servico->id,
                "usuario_id" => $cliente->id,
                "data_hora_inicio" => $dataHoraInicio,
                "data_hora_fim" => $dataHoraFim,
                "status" => "confirmado"
            ]);

            DB::commit();

            // Persistir o telefone do cliente na sessão para manter "logado"
            session(['cliente_telefone' => $telefoneLimpo]);

            return view("agendamento.confirmacao", compact("agendamento", "empresa", "servico", "cliente"));

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors([
                "error" => "Erro ao processar agendamento. Tente novamente."
            ]);
        }
    }

    /**
     * Verifica se o telefone já existe no banco de dados.
     */
    public function checkTelefone(Request $request)
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
    public function cancelarAgendamento(Request $request)
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
    public function mostrarCancelamento()
    {
        return view("agendamento.cancelamento");
    }

    /**
     * Mostra formulário de login da área do cliente ou redireciona se já estiver logado
     */
    public function mostrarLoginCliente()
    {
        // Verificar se o cliente já está logado na sessão
        $telefoneLogado = session('cliente_telefone');
        
        if ($telefoneLogado) {
            $cliente = Usuario::where("telefone", $telefoneLogado)->first();
            
            if ($cliente) {
                // Cliente está logado, redirecionar para a área do cliente
                return redirect()->route('cliente.area.logado');
            } else {
                // Cliente não existe mais, limpar a sessão
                session()->forget('cliente_telefone');
            }
        }
        
        // Cliente não está logado, mostrar formulário de login
        return view("cliente.login");
    }

    /**
     * Processa o login e exibe a área do cliente
     */
    public function areaCliente(Request $request)
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

        // Buscar todos os agendamentos do cliente, ordenados por data
        $agendamentos = Agendamento::with(["empresa", "servico"])
            ->where("usuario_id", $cliente->id)
            ->orderBy("data_hora_inicio", "desc")
            ->get();

        return view("cliente.area", compact("cliente", "agendamentos"));
    }

    /**
     * Verifica se há cliente logado na sessão e exibe a área do cliente
     */
    public function areaClienteLogado()
    {
        $telefoneLogado = session('cliente_telefone');
        
        if (!$telefoneLogado) {
            return redirect()->route('cliente.login');
        }

        $cliente = Usuario::where("telefone", $telefoneLogado)->first();

        if (!$cliente) {
            // Se o cliente não existe mais, limpar a sessão
            session()->forget('cliente_telefone');
            return redirect()->route('cliente.login');
        }

        // Buscar todos os agendamentos do cliente, ordenados por data
        $agendamentos = Agendamento::with(["empresa", "servico"])
            ->where("usuario_id", $cliente->id)
            ->orderBy("data_hora_inicio", "desc")
            ->get();

        return view("cliente.area", compact("cliente", "agendamentos"));
    }

    /**
     * Faz logout do cliente (limpa a sessão)
     */
    public function logoutCliente()
    {
        session()->forget('cliente_telefone');
        return redirect()->route('cliente.login')->with('success', 'Logout realizado com sucesso!');
    }
}



