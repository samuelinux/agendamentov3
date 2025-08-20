<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Servico;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DisponibilidadeService
{
    /**
     * Gera horários disponíveis para um serviço em uma data específica
     */
    public function gerarHorariosDisponiveis(Empresa $empresa, Servico $servico, Carbon $data): Collection
    {
        // 1. Validar se a data está dentro dos próximos 7 dias
        if (!$this->validarDataDentroDoLimite($data)) {
            return collect();
        }

        // 2. Verificar se há jornada de trabalho para o dia da semana
        $jornadasDoDia = $this->obterJornadasDoDia($empresa, $data);
        if ($jornadasDoDia->isEmpty()) {
            return collect();
        }

        // 3. Gerar todos os slots possíveis dentro das jornadas
        $slotsDisponiveis = $this->gerarSlotsDisponiveis($empresa, $jornadasDoDia, $data);

        // 4. Verificar exceções de agenda (feriados, férias, saidinhas)
        $slotsDisponiveis = $this->removerExcecoes($empresa, $slotsDisponiveis, $data);

        // 5. Remover horários já ocupados por agendamentos
        $slotsDisponiveis = $this->removerHorariosOcupados($empresa, $slotsDisponiveis, $data);

        // 6. Verificar disponibilidade para a duração do serviço
        $horariosDisponiveis = $this->verificarDisponibilidadeParaServico($slotsDisponiveis, $servico, $empresa);

        // 7. Aplicar regra de antecedência mínima
        $horariosDisponiveis = $this->aplicarAntecedenciaMinima($horariosDisponiveis, $empresa, $data);

        return $horariosDisponiveis;
    }

    /**
     * Valida se a data está dentro dos próximos 7 dias
     */
    private function validarDataDentroDoLimite(Carbon $data): bool
    {
        $hoje = Carbon::now()->startOfDay();
        $limite = $hoje->copy()->addDays(7);
        
        return $data->between($hoje, $limite);
    }

    /**
     * Obtém as jornadas de trabalho para o dia da semana específico
     */
    private function obterJornadasDoDia(Empresa $empresa, Carbon $data): Collection
    {
        $diaSemana = $data->dayOfWeek; // 0 = domingo, 1 = segunda, etc.
        
        return $empresa->jornadasTrabalho()
            ->where('dia_semana', $diaSemana)
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * Gera todos os slots possíveis dentro das jornadas de trabalho
     */
    private function gerarSlotsDisponiveis(Empresa $empresa, Collection $jornadas, Carbon $data): Collection
    {
        $slots = collect();
        $tamanhoSlot = $empresa->tamanho_slot_minutos;

        foreach ($jornadas as $jornada) {
            // Garantir que temos o formato correto de hora
            $horaInicio = $jornada->hora_inicio;
            $horaFim = $jornada->hora_fim;
            
            // Se já está no formato HH:MM, usar diretamente, senão extrair
            if (strlen($horaInicio) > 5) {
                $horaInicio = substr($horaInicio, 0, 5);
            }
            if (strlen($horaFim) > 5) {
                $horaFim = substr($horaFim, 0, 5);
            }
            
            try {
                $inicio = Carbon::createFromFormat("Y-m-d H:i", $data->format("Y-m-d") . " " . $horaInicio);
                $fim = Carbon::createFromFormat("Y-m-d H:i", $data->format("Y-m-d") . " " . $horaFim);
            } catch (\Exception $e) {
                // Se houver erro no formato, pular esta jornada
                continue;
            }

            $slotAtual = $inicio->copy();
            
            // Se a data for hoje, ajustar o início do slot para a hora atual ou próximo slot
            if ($data->isToday()) {
                $agora = Carbon::now();
                // Arredondar para o próximo slot válido a partir de agora
                $proximoSlotValido = $agora->copy()->addMinutes($tamanhoSlot - ($agora->minute % $tamanhoSlot));
                $proximoSlotValido->second = 0;

                if ($slotAtual->lt($proximoSlotValido)) {
                    $slotAtual = $proximoSlotValido;
                }
            }

            while ($slotAtual->lt($fim)) {
                $slots->push($slotAtual->copy());
                $slotAtual->addMinutes($tamanhoSlot);
            }
        }

        return $slots;
    }

    /**
     * Remove slots que coincidem com exceções de agenda
     */
    private function removerExcecoes(Empresa $empresa, Collection $slots, Carbon $data): Collection
    {
        $excecoes = $empresa->excecoesAgenda()
            ->where(function ($query) use ($data) {
                $query->where("tipo", "feriado")
                    ->whereDate("data_inicio", $data->format("Y-m-d"))
                    ->orWhere(function ($q) use ($data) {
                        $q->where("tipo", "ferias")
                            ->whereDate("data_inicio", "<=", $data->format("Y-m-d"))
                            ->whereDate("data_fim", ">=", $data->format("Y-m-d"));
                    })
                    ->orWhere(function ($q) use ($data) {
                        $q->where("tipo", "saidinha")
                            ->whereDate("data_inicio", $data->format("Y-m-d"));
                    });
            })
            ->get();

        foreach ($excecoes as $excecao) {
            if ($excecao->tipo === "feriado") {
                // Feriado bloqueia o dia todo
                return collect();
            } elseif ($excecao->tipo === "ferias") {
                // Férias bloqueiam o dia todo
                return collect();
            } elseif ($excecao->tipo === "saidinha") {
                // Saidinha bloqueia apenas o período específico
                $inicioSaidinha = Carbon::parse($excecao->data_inicio . " " . $excecao->hora_inicio);
                $fimSaidinha = Carbon::parse($excecao->data_inicio . " " . $excecao->hora_fim);
                
                $slots = $slots->filter(function ($slot) use ($inicioSaidinha, $fimSaidinha) {
                    return !$slot->between($inicioSaidinha, $fimSaidinha);
                });
            }
        }

        return $slots;
    }

    /**
     * Remove horários já ocupados por agendamentos confirmados
     */
    private function removerHorariosOcupados(Empresa $empresa, Collection $slots, Carbon $data): Collection
    {
        $agendamentos = $empresa->agendamentos()
            ->confirmados()
            ->whereDate("data_hora_inicio", $data->format("Y-m-d"))
            ->get();

        foreach ($agendamentos as $agendamento) {
            $inicioAgendamento = Carbon::parse($agendamento->data_hora_inicio);
            $fimAgendamento = Carbon::parse($agendamento->data_hora_fim);

            $slots = $slots->filter(function ($slot) use ($inicioAgendamento, $fimAgendamento) {
                return !$slot->between($inicioAgendamento, $fimAgendamento->subMinute());
            });
        }

        return $slots;
    }

    /**
     * Verifica se há slots consecutivos suficientes para o serviço
     */
    private function verificarDisponibilidadeParaServico(Collection $slots, Servico $servico, Empresa $empresa): Collection
    {
        $duracaoServico = $servico->duracao_minutos;
        $tamanhoSlot = $empresa->tamanho_slot_minutos;
        $slotsNecessarios = ceil($duracaoServico / $tamanhoSlot);
        
        $horariosDisponiveis = collect();
        $slotsOrdenados = $slots->sort();

        foreach ($slotsOrdenados as $index => $slot) {
            $slotsConsecutivos = collect([$slot]);
            
            // Verificar se há slots consecutivos suficientes
            for ($i = 1; $i < $slotsNecessarios; $i++) {
                $proximoSlot = $slot->copy()->addMinutes($tamanhoSlot * $i);
                
                if ($slotsOrdenados->contains($proximoSlot)) {
                    $slotsConsecutivos->push($proximoSlot);
                } else {
                    break;
                }
            }

            // Se temos slots consecutivos suficientes, adicionar o horário
            if ($slotsConsecutivos->count() >= $slotsNecessarios) {
                $horariosDisponiveis->push([
                    "inicio" => $slot->format("H:i"),
                    "fim" => $slot->copy()->addMinutes($duracaoServico)->format("H:i"),
                    "data_hora_inicio" => $slot->format("Y-m-d H:i:s"),
                    "data_hora_fim" => $slot->copy()->addMinutes($duracaoServico)->format("Y-m-d H:i:s")
                ]);
            }
        }

        return $horariosDisponiveis;
    }

    /**
     * Aplica a regra de antecedência mínima
     */
    private function aplicarAntecedenciaMinima(Collection $horarios, Empresa $empresa, Carbon $data): Collection
    {
        if ($empresa->antecedencia_minima_horas === 0) {
            return $horarios;
        }

        $agora = Carbon::now();
        $limiteMinimo = $agora->addHours($empresa->antecedencia_minima_horas);

        return $horarios->filter(function ($horario) use ($limiteMinimo) {
            $dataHoraInicio = Carbon::parse($horario['data_hora_inicio']);
            return $dataHoraInicio->gte($limiteMinimo);
        });
    }

    /**
     * Obtém os próximos 7 dias com horários disponíveis
     */
    public function obterProximosDiasDisponiveis(Empresa $empresa, Servico $servico): Collection
    {
        $diasDisponiveis = collect();
        $hoje = Carbon::now()->startOfDay();

        for ($i = 0; $i < 7; $i++) {
            $data = $hoje->copy()->addDays($i);
            $horarios = $this->gerarHorariosDisponiveis($empresa, $servico, $data);
            
            if ($horarios->isNotEmpty()) {
                $diasDisponiveis->push([
                    'data' => $data->format('Y-m-d'),
                    'data_formatada' => $data->format('d/m/Y'),
                    'dia_semana' => $data->dayName,
                    'horarios' => $horarios->values()
                ]);
            }
        }

        return $diasDisponiveis;
    }
}

