<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agendamento;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view("admin.login");
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $credentials = $request->only("email", "password");
        
        // Tentar autenticar apenas usuários do tipo admin
        if (Auth::guard("web")->attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->tipo === "admin") {
                $request->session()->regenerate();
                
                // Redirecionar baseado no tipo de admin
                if ($user->empresa_id === null) {
                    // Super Admin
                    return redirect()->route("admin.empresas.index");
                } else {
                    // Admin da Empresa
                    return redirect()->route("admin.dashboard");
                }
            } else {
                Auth::logout();
                return back()->withErrors([
                    "email" => "Acesso negado. Apenas administradores podem acessar.",
                ]);
            }
        }

        return back()->withErrors([
            "email" => "Credenciais inválidas.",
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route("admin.login");
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        if ($user->empresa_id === null) {
            // Super Admin - redirecionar para gestão de empresas
            return redirect()->route("admin.empresas.index");
        }
        
        // Admin da Empresa - mostrar dashboard
        $empresa = $user->empresa;
        $perPage = $request->get("per_page", 10);
        
        // Agendamentos do dia
        $agendamentosDoDia = Agendamento::where("empresa_id", $empresa->id)
            ->whereDate("data_hora_inicio", today())
            ->with(["servico", "usuario"])
            ->orderBy("data_hora_inicio", "asc")
            ->paginate($perPage);
            
        // Calcular valor ganho (agendamentos realizados)
        $valorGanho = Agendamento::where("agendamentos.empresa_id", $empresa->id)
            ->whereDate("data_hora_inicio", today())
            ->where("status", "realizado") // Assumindo que \"realizado\" é o status para agendamentos concluídos
            ->join("servicos", "agendamentos.servico_id", "=", "servicos.id")
            ->sum("servicos.valor");

        // Calcular valor futuro (agendamentos não cancelados)
        $valorFuturo = Agendamento::where("agendamentos.empresa_id", $empresa->id)
            ->whereDate("data_hora_inicio", today())
            ->whereIn("status", ["agendado", "confirmado", "realizado"])
            ->join("servicos", "agendamentos.servico_id", "=", "servicos.id")
            ->sum("servicos.valor");

        return view("admin.dashboard", compact(
            "empresa",
            "agendamentosDoDia",
            "valorGanho",
            "valorFuturo",
            "perPage"
        ));
    }

    public function showWorkingHoursForm()
    {
        $user = Auth::user();
        $empresa = $user->empresa;
        $jornadas = $empresa->jornadasTrabalho->keyBy("dia_semana");

        $diasSemana = [
            1 => "Segunda-feira",
            2 => "Terça-feira",
            3 => "Quarta-feira",
            4 => "Quinta-feira",
            5 => "Sexta-feira",
            6 => "Sábado",
            0 => "Domingo",
        ];

        return view("admin.working_hours", compact("empresa", "jornadas", "diasSemana"));
    }

    public function saveWorkingHours(Request $request)
    {
        $user = Auth::user();
        $empresa = $user->empresa;

        // Validação customizada para permitir campos vazios
        $validatedData = $request->validate([
            "jornadas" => "required|array",
            "jornadas.*.dia_semana" => "required|integer|between:0,6",
            "jornadas.*.hora_inicio" => "nullable|date_format:H:i",
            "jornadas.*.hora_fim" => "nullable|date_format:H:i",
        ]);

        // Validação adicional para verificar se hora_fim é posterior à hora_inicio
        foreach ($request->jornadas as $index => $jornadaData) {
            if (!empty($jornadaData["hora_inicio"]) && !empty($jornadaData["hora_fim"])) {
                if ($jornadaData["hora_inicio"] >= $jornadaData["hora_fim"]) {
                    return back()->withErrors([
                        "jornadas.{$index}.hora_fim" => "A hora de fim deve ser posterior à hora de início."
                    ])->withInput();
                }
            }
        }

        foreach ($request->jornadas as $jornadaData) {
            if (empty($jornadaData["hora_inicio"]) && empty($jornadaData["hora_fim"])) {
                // Se ambos os campos estiverem vazios, deletar a jornada existente para este dia
                $empresa->jornadasTrabalho()->where("dia_semana", $jornadaData["dia_semana"])->delete();
            } else {
                // Se houver dados, atualizar ou criar
                $empresa->jornadasTrabalho()->updateOrCreate(
                    ["dia_semana" => $jornadaData["dia_semana"]],
                    [
                        "hora_inicio" => $jornadaData["hora_inicio"],
                        "hora_fim" => $jornadaData["hora_fim"],
                    ]
                );
            }
        }

        return redirect()->route("admin.working_hours.form")->with("success", "Carga horária atualizada com sucesso!");
    }



}

