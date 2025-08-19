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
        
        // 3. Gerar todos os slots possíveis dentro das jornadas,
        // ajustando para não incluir horários passados no dia atual
        $slotsDisponiveis = $this->gerarSlotsDisponiveis($empresa, $jornadasDoDia, $data);

        // 4. Verificar exceções de agenda (feriados, férias, saidinhas)
        $slotsDisponiveis = $this->removerExcecoes($empresa, $slotsDisponiveis, $data);

        // 5. Remover horários já ocupados por agendamentos
        $slotsDisponiveis = $this->removerHorariosOcupados($empresa, $slotsDisponiveis, $data);

        // 6. Verificar disponibilidade para a duração do serviço (encaixe)
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
    /**
 * Gera todos os slots possíveis dentro das jornadas de trabalho
 */
private function gerarSlotsDisponiveis(Empresa $empresa, Collection $jornadas, Carbon $data): Collection
    {
        $slots = collect();
        $tamanhoSlot = $empresa->tamanho_slot_minutos;

        foreach ($jornadas as $jornada) {
            // Extrair início e fim em formato HH:MM
            $horaInicio = strlen($jornada->hora_inicio) > 5 ? substr($jornada->hora_inicio, 0, 5) : $jornada->hora_inicio;
            $horaFim = strlen($jornada->hora_fim) > 5 ? substr($jornada->hora_fim, 0, 5) : $jornada->hora_fim;

            try {
                $inicio = Carbon::createFromFormat('Y-m-d H:i', $data->format('Y-m-d') . ' ' . $horaInicio);
                $fim = Carbon::createFromFormat('Y-m-d H:i', $data->format('Y-m-d') . ' ' . $horaFim);
            } catch (\Exception $e) {
                // Pula jornada com formato inválido
                continue;
            }

            $slotAtual = $inicio->copy();

            // Se a data é hoje, não exibir horários passados
            if ($data->isToday()) {
                $agora = Carbon::now();
                // Ajusta apenas se agora está dentro da jornada
                if ($agora->between($inicio, $fim)) {
                    // Próximo slot múltiplo do tamanho do slot
                    $minRest = $agora->minute % $tamanhoSlot;
                    $proximoSlotValido = $agora->copy()->addMinutes($minRest > 0 ? ($tamanhoSlot - $minRest) : 0);
                    $proximoSlotValido->second = 0;

                    if ($slotAtual->lt($proximoSlotValido)) {
                        $slotAtual = $proximoSlotValido;
                    }
                }

                // Se a jornada já acabou, não gera nada
                if ($fim->lte($agora)) {
                    continue;
                }
            }

            // Gera os slots dentro desta jornada
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

        $horariosDisponiveis = collect();
        $slotsOrdenados = $slots->sort()->values();

        // Agrupa slots consecutivos (intervalo de jornada) pela diferença de tempo
        $grupos = collect();
        $grupoAtual = collect();

        foreach ($slotsOrdenados as $index => $slot) {
            if ($index === 0) {
                $grupoAtual->push($slot);
            } else {
                $slotAnterior = $slotsOrdenados[$index - 1];
                // Se a diferença é exatamente o tamanho do slot, mesmo grupo
                if ($slot->diffInMinutes($slotAnterior) === $tamanhoSlot) {
                    $grupoAtual->push($slot);
                } else {
                    $grupos->push($grupoAtual);
                    $grupoAtual = collect([$slot]);
                }
            }
        }
        // Adiciona o último grupo
        if ($grupoAtual->isNotEmpty()) {
            $grupos->push($grupoAtual);
        }

        // Para cada grupo (jornada), verifica se o serviço cabe
        foreach ($grupos as $grupo) {
            // Define o fim dessa jornada (último slot + tamanhoSlot)
            $ultimoSlot = $grupo->last();
            $fimDaJornada = $ultimoSlot->copy()->addMinutes($tamanhoSlot);

            foreach ($grupo as $slot) {
                $horaFimPrevista = $slot->copy()->addMinutes($duracaoServico);
                if ($horaFimPrevista->lte($fimDaJornada)) {
                    $horariosDisponiveis->push([
                        'inicio' => $slot->format('H:i'),
                        'fim' => $horaFimPrevista->format('H:i'),
                        'data_hora_inicio' => $slot->format('Y-m-d H:i:s'),
                        'data_hora_fim' => $horaFimPrevista->format('Y-m-d H:i:s')
                    ]);
                }
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
        $proximosDias = 7;
        for ($i = 0; $i < $proximosDias; $i++) {
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

