<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            // Verificar se é admin da empresa
            if ($user->empresa_id === null) {
                abort(403, 'Acesso negado. Esta área é apenas para administradores de empresa.');
            }

            return $next($request);
        });
    }

    public function atendimentos(Request $request)
    {
        $empresa = auth()->user()->empresa;
        $perPage = $request->get('per_page', 10);

        // Definir período padrão (últimos 30 dias)
        $dataInicio = $request->get('data_inicio', now()->subDays(30)->format('Y-m-d\TH:i'));
        $dataFim = $request->get('data_fim', now()->format('Y-m-d\TH:i'));

        // Validar datas
        try {
            $dataInicioCarbon = Carbon::createFromFormat('Y-m-d\TH:i', $dataInicio);
            $dataFimCarbon = Carbon::createFromFormat('Y-m-d\TH:i', $dataFim);
        } catch (\Exception $e) {
            return back()->withErrors(['data' => 'Formato de data inválido.']);
        }

        // Buscar agendamentos no período
        $agendamentos = Agendamento::where('empresa_id', $empresa->id)
            ->whereBetween('data_hora_inicio', [$dataInicioCarbon, $dataFimCarbon])
            ->with(['servico', 'usuario'])
            ->orderBy('data_hora_inicio', 'desc')
            ->paginate($perPage);

        // Calcular ganhos totais do período
        $ganhosTotais = Agendamento::where('empresa_id', $empresa->id)
            ->whereBetween('data_hora_inicio', [$dataInicioCarbon, $dataFimCarbon])
            ->pagos() // scope que já filtra status = 'pago'
            ->sum('valor_pago');

        // Agrupar agendamentos por data para exibição
        $agendamentosPorData = $agendamentos->groupBy(function ($agendamento) {
            return $agendamento->data_hora_inicio->format('Y-m-d');
        });

        // Calcular valor ganho (agendamentos realizados)
        $valorGanho = Agendamento::where('agendamentos.empresa_id', $empresa->id)
            ->whereDate('data_hora_inicio', today())
            ->where('status', 'pago') // Assumindo que \"realizado\" é o status para agendamentos concluídos
            ->sum('valor_pago');

        // Calcular valor futuro (agendamentos não cancelados)
        $valorFuturo = Agendamento::where('agendamentos.empresa_id', $empresa->id)
            ->whereDate('data_hora_inicio', today())
            ->whereIn('status', ['agendado', 'confirmado', 'realizado', 'pago']) // Exclui apenas cancelados
            ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->sum(DB::raw('servicos.valor + agendamentos.valor_pago'));

        // Agendamentos do dia
        $agendamentosDoDia = Agendamento::where('empresa_id', $empresa->id)
            ->whereDate('data_hora_inicio', today())
            ->with(['servico', 'usuario'])
            ->orderBy('data_hora_inicio', 'asc')
            ->paginate($perPage);

        return view('admin.relatorios.atendimentos', compact(
            'agendamentos',
            'agendamentosPorData',
            'ganhosTotais',
            'dataInicio',
            'dataFim',
            'perPage',
            'empresa',
            'valorGanho',
            'valorFuturo',
            'agendamentosDoDia'
        ));
    }
}
