@extends('admin.layout')

@section('title', 'Carga Horária Semanal')
@section('header', 'Carga Horária Semanal - ' . $empresa->nome)

@section('content')
    <div class="card">
        <h2>Definir Carga Horária Semanal</h2>
        <p>Configure um ou vários turnos por dia.</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.working_hours.save') }}">
            @csrf

            <div class="working-hours-container">
                @foreach ($diasSemana as $diaSemana => $nomeDia)
                    @php
                        // Fonte de verdade para renderização:
                        // 1) old() pós-validação; 2) jornadasPorDia vindas do controller; 3) 1 turno vazio
                        $oldTurnos = old("jornadas.$diaSemana", null);
                        // $jornadasPorDia deve ser Collection<Grouped> pelo controller (groupBy)
                        $turnosExistentes =
                            isset($jornadasPorDia) && $jornadasPorDia->has($diaSemana)
                                ? $jornadasPorDia[$diaSemana]
                                    ->map(
                                        fn($j) => [
                                            'hora_inicio' => \Illuminate\Support\Str::of($j->hora_inicio)->substr(0, 5),
                                            'hora_fim' => \Illuminate\Support\Str::of($j->hora_fim)->substr(0, 5),
                                            'dia_semana' => $j->dia_semana,
                                        ],
                                    )
                                    ->values()
                                    ->toArray()
                                : [];

                        $turnos = is_array($oldTurnos)
                            ? $oldTurnos
                            : (!empty($turnosExistentes)
                                ? $turnosExistentes
                                : [['hora_inicio' => '', 'hora_fim' => '', 'dia_semana' => $diaSemana]]);
                    @endphp

                    <div class="day-block" data-dia="{{ $diaSemana }}"
                        style="margin-bottom:1rem;padding:1rem;border:1px solid #ddd;border-radius:8px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                            <div style="font-weight:bold;font-size:1.05rem;">
                                {{ $nomeDia }}
                            </div>
                            <div style="display:flex;gap:.5rem;">
                                <button type="button" class="btn btn-sm btn-secondary"
                                    onclick="clearDay({{ $diaSemana }})">Limpar dia</button>
                                <button type="button" class="btn btn-sm btn-primary"
                                    onclick="addTurno({{ $diaSemana }})">+ Adicionar turno</button>
                            </div>
                        </div>

                        <div class="turnos" id="turnos-{{ $diaSemana }}">
                            @foreach ($turnos as $idx => $turno)
                                <div class="turno-row"
                                    style="display:flex;gap:1rem;align-items:flex-end;margin-bottom:.75rem;border:1px dashed #ccc;padding:.75rem;border-radius:6px;">
                                    <div>
                                        <label style="display:block;font-size:.9rem;margin-bottom:.25rem;">Início</label>
                                        <input type="time"
                                            name="jornadas[{{ $diaSemana }}][{{ $idx }}][hora_inicio]"
                                            value="{{ \Illuminate\Support\Str::of($turno['hora_inicio'] ?? '')->substr(0, 5) }}"
                                            class="form-control" style="width:130px;">
                                        @error("jornadas.$diaSemana.$idx.hora_inicio")
                                            <div class="text-danger" style="font-size:.85rem;margin-top:.25rem;">
                                                {{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label style="display:block;font-size:.9rem;margin-bottom:.25rem;">Fim</label>
                                        <input type="time"
                                            name="jornadas[{{ $diaSemana }}][{{ $idx }}][hora_fim]"
                                            value="{{ \Illuminate\Support\Str::of($turno['hora_fim'] ?? '')->substr(0, 5) }}"
                                            class="form-control" style="width:130px;">
                                        @error("jornadas.$diaSemana.$idx.hora_fim")
                                            <div class="text-danger" style="font-size:.85rem;margin-top:.25rem;">
                                                {{ $message }}</div>
                                        @enderror
                                    </div>

                                    <input type="hidden"
                                        name="jornadas[{{ $diaSemana }}][{{ $idx }}][dia_semana]"
                                        value="{{ $diaSemana }}">

                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="removeTurno(this)">Remover</button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Erros "genéricos" do dia (caso você valide sobreposição, etc) --}}
                        @error("jornadas.$diaSemana")
                            <div class="text-danger" style="font-size:.9rem;">{{ $message }}</div>
                        @enderror

                        {{-- Template invisível para novos turnos desse dia --}}
                        <template id="tpl-turno-{{ $diaSemana }}">
                            <div class="turno-row"
                                style="display:flex;gap:1rem;align-items:flex-end;margin-bottom:.75rem;border:1px dashed #ccc;padding:.75rem;border-radius:6px;">
                                <div>
                                    <label style="display:block;font-size:.9rem;margin-bottom:.25rem;">Início</label>
                                    <input type="time" class="form-control" style="width:130px;">
                                </div>
                                <div>
                                    <label style="display:block;font-size:.9rem;margin-bottom:.25rem;">Fim</label>
                                    <input type="time" class="form-control" style="width:130px;">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="removeTurno(this)">Remover</button>
                            </div>
                        </template>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:1.25rem;display:flex;gap:1rem;">
                <button type="submit" class="btn btn-primary">Salvar Carga Horária</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Voltar ao Dashboard</a>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:2rem;">
        <h3>Instruções</h3>
        <ul style="margin-left:2rem;">
            <li>Adicione quantos turnos quiser por dia.</li>
            <li>Deixe o dia sem turnos para indicar que não há atendimento.</li>
            <li>Evite sobreposição de turnos no mesmo dia.</li>
            <li>A hora de fim deve ser posterior à de início.</li>
        </ul>
    </div>

    <script>
        function nextIndexForDay(dia) {
            const cont = document.getElementById('turnos-' + dia);
            const rows = cont.querySelectorAll('.turno-row');
            return rows.length; // usa length como próximo índice
        }

        function addTurno(dia) {
            const tpl = document.getElementById('tpl-turno-' + dia);
            const clone = tpl.content.cloneNode(true);
            const idx = nextIndexForDay(dia);

            const inputs = clone.querySelectorAll('input[type="time"]');
            const inicio = inputs[0];
            const fim = inputs[1];

            inicio.name = `jornadas[${dia}][${idx}][hora_inicio]`;
            fim.name = `jornadas[${dia}][${idx}][hora_fim]`;

            // hidden dia_semana
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `jornadas[${dia}][${idx}][dia_semana]`;
            hidden.value = dia;
            clone.firstElementChild.appendChild(hidden);

            document.getElementById('turnos-' + dia).appendChild(clone);
        }

        function removeTurno(btn) {
            const row = btn.closest('.turno-row');
            row?.remove();
        }

        function clearDay(dia) {
            const cont = document.getElementById('turnos-' + dia);
            cont.innerHTML = '';
            addTurno(dia); // deixa um turno vazio por padrão
        }
    </script>
@endsection
