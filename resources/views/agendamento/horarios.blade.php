@extends('layout')

@section('title', 'Agendar ' . $servico->nome)
@section('header', 'Agendar ' . $servico->nome)
@section('subtitle', $empresa->nome)

@section('content')
    @php
        $telefoneLogado = session('cliente_telefone');
    @endphp
    <a href="{{ route('empresa', $empresa->slug) }}" class="back-link">
        ← Voltar para serviços
    </a>

    <div style="background: #f7fafc; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 0.5rem 0; color: #2d3748;">{{ $servico->nome }}</h3>
        @if ($servico->descricao)
            <p style="margin: 0 0 0.5rem 0; color: #718096;">{{ $servico->descricao }}</p>
        @endif
        <p style="margin: 0; color: #4a5568;"><strong>Duração:</strong> {{ $servico->duracao_minutos }} minutos</p>
    </div>

    @if ($diasDisponiveis->isEmpty())
        <div class="alert alert-error">
            <p><strong>Nenhum horário disponível</strong></p>
            <p>Não há horários disponíveis para este serviço nos próximos 7 dias. Tente novamente mais tarde ou entre em
                contato com a empresa.</p>
        </div>
    @else
        <form id="agendamentoForm" method="POST" action="{{ route('agendamento.confirmar', [$empresa, $servico]) }}">
            @csrf
            <input type="hidden" name="data_hora_inicio" id="dataHoraInicio">

            <h3 style="margin-bottom: 1rem; color: #2d3748;">Escolha um horário:</h3>

            <div id="diasAccordion" style="display: grid; gap: .75rem;">
                @foreach ($diasDisponiveis as $i => $dia)
                    <button type="button" class="dia-header" data-index="{{ $i }}"
                        onclick="toggleDia({{ $i }})" aria-expanded="false"
                        style="width:100%; display:flex; justify-content:space-between; align-items:center; gap:.5rem; padding:.75rem 1rem; border:1px solid #e2e8f0; background:#fff; border-radius:8px; cursor:pointer;">
                        <span style="font-weight:600; color:#2d3748;">
                            {{ $dia['dia_semana'] }}
                        </span>
                        <span style="color:#4a5568;">
                            {{ $dia['data_formatada'] }}
                        </span>
                        <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            style="transition: transform .2s ease; color:#718096;">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div id="slots-{{ $i }}" class="dia-slots" style="display:none; padding:.5rem 0 .25rem;">
                        <div class="slots-grid" style="display:flex; flex-wrap:wrap; gap:.5rem;">
                            @foreach ($dia['horarios'] as $horario)
                                <div class="time-slot" data-datetime="{{ $horario['data_hora_inicio'] }}"
                                    onclick="selecionarHorario(this)">
                                    {{ $horario['inicio'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>


            <div id="dadosCliente"
                style="display: none; margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">
                <h3 style="margin-bottom: 1rem; color: #2d3748;">Seus dados:</h3>

                @if ($telefoneLogado)
                    <div class="form-group">
                        <label for="telefone_cliente">Telefone (WhatsApp):</label>
                        <input type="tel" id="telefone_cliente" name="telefone_cliente" class="form-control"
                            value="{{ $telefoneLogado }}" readonly>
                    </div>
                    <div id="nomeClienteGroup" class="form-group">
                        <label for="nome_cliente">Nome completo:</label>
                        <input type="text" id="nome_cliente" name="nome_cliente" class="form-control"
                            placeholder="Seu nome" readonly>
                    </div>
                    <div id="horarioSelecionado"
                        style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                        <p style="margin: 0; color: #234e52;"><strong>Horário selecionado:</strong> <span
                                id="horarioTexto"></span></p>
                    </div>
                    <button type="submit" class="btn btn-success" id="btnConfirmarAgendamentoLogado">
                        Confirmar Agendamento
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cancelarSelecao()"
                        style="margin-left: .5rem;">
                        Escolher outro horário
                    </button>
                @else
                    <div class="form-group">
                        <label for="telefone_cliente">Telefone (WhatsApp):</label>
                        <input type="tel" id="telefone_cliente" name="telefone_cliente" class="form-control"
                            placeholder="(11) 99999-9999" required>
                    </div>

                    <div id="nomeClienteGroup" class="form-group" style="display: none;">
                        <label for="nome_cliente">Nome completo:</label>
                        <input type="text" id="nome_cliente" name="nome_cliente" class="form-control"
                            placeholder="Digite seu nome completo">
                    </div>

                    <div id="horarioSelecionado"
                        style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                        <p style="margin: 0; color: #234e52;"><strong>Horário selecionado:</strong> <span
                                id="horarioTexto"></span></p>
                    </div>

                    <button type="button" class="btn btn-primary" id="btnVerificarTelefone" onclick="verificarTelefone()">
                        Verificar Telefone
                    </button>

                    <button type="submit" class="btn btn-success" id="btnConfirmarAgendamento"
                        style="display: none; margin-left: .5rem;">
                        Confirmar Agendamento
                    </button>

                    <button type="button" class="btn btn-secondary" onclick="cancelarSelecao()"
                        style="margin-left: .5rem;">
                        Escolher outro horário
                    </button>
                @endif
            </div>
        </form>
    @endif
@endsection

@section('scripts')
    <script>
        let horarioSelecionado = null;
        let telefoneLogado = "{{ $telefoneLogado }}";
/**/
let diaAberto = null;

    function toggleDia(index) {
        const atual = document.getElementById(`slots-${index}`);
        const headerAtual = document.querySelector(`.dia-header[data-index="${index}"]`);
        const abertoIndex = diaAberto;

        // Fecha o dia anteriormente aberto
        if (abertoIndex !== null && abertoIndex !== index) {
            const anterior = document.getElementById(`slots-${abertoIndex}`);
            const headerAnterior = document.querySelector(`.dia-header[data-index="${abertoIndex}"]`);
            if (anterior) anterior.style.display = "none";
            if (headerAnterior) headerAnterior.setAttribute("aria-expanded", "false");
            const chevAnt = headerAnterior?.querySelector(".chevron");
            if (chevAnt) chevAnt.style.transform = "rotate(0deg)";
        }

        // Alterna o atual
        const isOpen = atual.style.display === "block";
        if (isOpen) {
            atual.style.display = "none";
            headerAtual.setAttribute("aria-expanded", "false");
            headerAtual.querySelector(".chevron").style.transform = "rotate(0deg)";
            diaAberto = null;
        } else {
            atual.style.display = "block";
            headerAtual.setAttribute("aria-expanded", "true");
            headerAtual.querySelector(".chevron").style.transform = "rotate(180deg)";
            diaAberto = index;
            atual.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }
        function selecionarHorario(elemento) {
            // Remover seleção anterior
            document.querySelectorAll(".time-slot").forEach(slot => {
                slot.classList.remove("selected");
            });

            // Selecionar novo horário
            elemento.classList.add("selected");
            horarioSelecionado = elemento.dataset.datetime;

            // Atualizar campo hidden
            document.getElementById("dataHoraInicio").value = horarioSelecionado;

            // Mostrar formulário de dados
            document.getElementById("dadosCliente").style.display = "block";

            // Atualizar texto do horário selecionado
            const dataHora = new Date(horarioSelecionado);
            const opcoes = {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            };
            document.getElementById("horarioTexto").textContent = dataHora.toLocaleDateString("pt-BR", opcoes);

            // Scroll suave para o formulário
            document.getElementById("dadosCliente").scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

            // Ajustar UI conforme estado de login
            if (telefoneLogado) {
                const tel = document.getElementById("telefone_cliente");
                if (tel) {
                    tel.value = telefoneLogado;
                    tel.setAttribute("readonly", true);
                }

                const btnVerificar = document.getElementById("btnVerificarTelefone");
                if (btnVerificar) btnVerificar.style.display = "none";

                // Mostrar algum botão de confirmar (o específico para logado ou o padrão)
                const btnConfirmLogado = document.getElementById("btnConfirmarAgendamentoLogado");
                const btnConfirm = document.getElementById("btnConfirmarAgendamento");
                if (btnConfirmLogado) btnConfirmLogado.style.display = "inline-block";
                if (btnConfirm) btnConfirm.style.display = "inline-block";

                // Mostrar nome apenas informativo
                const nomeGroup = document.getElementById("nomeClienteGroup");
                if (nomeGroup) nomeGroup.style.display = "block";

                // Buscar nome do cliente para exibir de forma informativa
                fetch("/api/check-telefone", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            telefone: telefoneLogado
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success && data.exists && data.nome) {
                            const nome = document.getElementById("nome_cliente");
                            if (nome) {
                                nome.value = data.nome;
                                nome.setAttribute("readonly", true);
                            }
                        }
                    })
                    .catch(() => {});
            } else {
                const btnVerificar = document.getElementById("btnVerificarTelefone");
                if (btnVerificar) btnVerificar.style.display = "inline-block";
                const btnConfirm = document.getElementById("btnConfirmarAgendamento");
                if (btnConfirm) btnConfirm.style.display = "none";
                const nome = document.getElementById("nome_cliente");
                if (nome) {
                    nome.removeAttribute("required");
                    nome.removeAttribute("readonly");
                    nome.value = "";
                }
                const nomeGroup = document.getElementById("nomeClienteGroup");
                if (nomeGroup) nomeGroup.style.display = "none";
            }
        }

        async function verificarTelefone() {
            const telefoneInput = document.getElementById("telefone_cliente");
            const telefone = telefoneInput.value.replace(/\D/g, "");

            if (!telefone || telefone.length < 10) {
                alert("Por favor, digite um telefone válido com pelo menos 10 dígitos.");
                return;
            }

            // Mostrar loading
            const btnVerificar = document.getElementById("btnVerificarTelefone");
            const textoOriginal = btnVerificar.textContent;
            btnVerificar.textContent = "Verificando...";
            btnVerificar.disabled = true;

            try {
                const response = await fetch("/api/check-telefone", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        telefone: telefone
                    })
                });

                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }

                const data = await response.json();

                if (data.success === false) {
                    throw new Error(data.message || "Erro na verificação do telefone");
                }

                if (data.exists) {
                    // Usuário existente - ocultar verificação e permitir confirmar
                    document.getElementById("nome_cliente").value = data.nome || "";
                    document.getElementById("nomeClienteGroup").style.display = "block";
                    document.getElementById("nome_cliente").setAttribute("readonly", true);
                    btnVerificar.style.display = "none";
                    document.getElementById("btnConfirmarAgendamento").style.display = "inline-block";
                } else {
                    // Novo usuário - pedir nome
                    document.getElementById("nomeClienteGroup").style.display = "block";
                    const nome = document.getElementById("nome_cliente");
                    nome.value = "";
                    nome.removeAttribute("readonly");
                    nome.setAttribute("required", true);
                    btnVerificar.style.display = "none";
                    document.getElementById("btnConfirmarAgendamento").style.display = "inline-block";
                    nome.focus();
                }
            } catch (error) {
                alert("Ocorreu um erro ao verificar o telefone: " + error.message + ". Tente novamente.");
            } finally {
                btnVerificar.textContent = textoOriginal;
                btnVerificar.disabled = false;
            }
        }

        function cancelarSelecao() {
            // Remover todas as seleções
            document.querySelectorAll(".time-slot").forEach(slot => {
                slot.classList.remove("selected");
            });

            // Esconder formulário
            document.getElementById("dadosCliente").style.display = "none";

            // Limpar dados
            horarioSelecionado = null;
            document.getElementById("dataHoraInicio").value = "";
            const nome = document.getElementById("nome_cliente");
            if (nome) {
                nome.value = "";
                nome.removeAttribute("required");
                nome.removeAttribute("readonly");
            }
            const tel = document.getElementById("telefone_cliente");
            if (tel && !telefoneLogado) {
                tel.value = "";
            }
            const btnVerificar = document.getElementById("btnVerificarTelefone");
            if (btnVerificar) btnVerificar.style.display = "inline-block";
            const btnConfirm = document.getElementById("btnConfirmarAgendamento");
            if (btnConfirm) btnConfirm.style.display = "none";

            // Scroll para o topo
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }

        // Máscara para telefone
        (document.getElementById("telefone_cliente"))?.addEventListener("input", function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
                if (value.length < 14) {
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, "($1) $2-$3");
                }
            }
            e.target.value = value;
        });
    </script>
@endsection
