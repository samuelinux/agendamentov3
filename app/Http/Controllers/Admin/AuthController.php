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

        return view("admin.dashboard", compact(
            "empresa",
            "perPage",
            'agendamentosDoDia'
        ));
    }

    public function showWorkingHoursForm()
{
    $user = Auth::user();
    $empresa = $user->empresa;

    $jornadasPorDia = $empresa->jornadasTrabalho()
        ->orderBy('dia_semana')
        ->orderBy('hora_inicio')
        ->get()
        ->groupBy('dia_semana');

    $diasSemana = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        0 => 'Domingo',
    ];

    return view('admin.working_hours', compact('empresa', 'jornadasPorDia', 'diasSemana'));
}

    public function saveWorkingHours(Request $request)
{
    $user = Auth::user();
    $empresa = $user->empresa;

    $data = $request->validate([
        'jornadas'                    => 'required|array',
        'jornadas.*'                  => 'array',
        'jornadas.*.*.dia_semana'     => 'required|integer|between:0,6',
        'jornadas.*.*.hora_inicio'    => 'nullable|date_format:H:i',
        'jornadas.*.*.hora_fim'       => 'nullable|date_format:H:i',
    ]);

    foreach ($data['jornadas'] as $diaSemana => $turnos) {
        // remove turnos vazios e valida pares completos
        $turnos = collect($turnos ?? [])
            ->filter(fn($t) => !empty($t['hora_inicio']) || !empty($t['hora_fim']))
            ->values();

        foreach ($turnos as $idx => $t) {
            if (empty($t['hora_inicio']) || empty($t['hora_fim'])) {
                return back()->withErrors([
                    "jornadas.$diaSemana.$idx.hora_inicio" => 'Informe a hora de início.',
                    "jornadas.$diaSemana.$idx.hora_fim"    => 'Informe a hora de fim.',
                ])->withInput();
            }
            if ($t['hora_inicio'] >= $t['hora_fim']) {
                return back()->withErrors([
                    "jornadas.$diaSemana.$idx.hora_fim" => 'A hora de fim deve ser posterior à de início.',
                ])->withInput();
            }
        }

        // (opcional) validar sobreposição de turnos
        $slots = $turnos->map(fn($t) => [$t['hora_inicio'], $t['hora_fim']])->sort()->values();
        for ($i=1; $i < $slots->count(); $i++) {
            if ($slots[$i-1][1] > $slots[$i][0]) {
                return back()->withErrors([
                    "jornadas.$diaSemana" => 'Os turnos não podem se sobrepor.',
                ])->withInput();
            }
        }

        // persistência: limpa turnos do dia e regrava
        $empresa->jornadasTrabalho()->where('dia_semana', $diaSemana)->delete();

        foreach ($turnos as $t) {
            $empresa->jornadasTrabalho()->create([
                'dia_semana'  => (int)$diaSemana,
                'hora_inicio' => $t['hora_inicio'],
                'hora_fim'    => $t['hora_fim'],
            ]);
        }
    }

    return redirect()->route('admin.working_hours.form')->with('success', 'Carga horária atualizada com sucesso!');
}




}

