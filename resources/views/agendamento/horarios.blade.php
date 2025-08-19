@extends('layout')

@section('title', 'Agendar ' . $servico->nome)
@section('header', 'Agendar ' . $servico->nome)
@section('subtitle', $empresa->nome)

@section('content')
<a href="{{ route('empresa', $empresa->slug) }}" class="back-link">
    ← Voltar para serviços
</a>

<div style="background: #f7fafc; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
    <h3 style="margin: 0 0 0.5rem 0; color: #2d3748;">{{ $servico->nome }}</h3>
    @if($servico->descricao)
        <p style="margin: 0 0 0.5rem 0; color: #718096;">{{ $servico->descricao }}</p>
    @endif
    <p style="margin: 0; color: #4a5568;"><strong>Duração:</strong> {{ $servico->duracao_minutos }} minutos</p>
</div>

@if($diasDisponiveis->isEmpty())
    <div class="alert alert-error">
        <p><strong>Nenhum horário disponível</strong></p>
        <p>Não há horários disponíveis para este serviço nos próximos 7 dias. Tente novamente mais tarde ou entre em contato com a empresa.</p>
    </div>
@else
    <form id="agendamentoForm" method="POST" action="{{ route('agendamento.confirmar', [$empresa, $servico]) }}">
        @csrf
        <input type="hidden" name="data_hora_inicio" id="dataHoraInicio">
        
        <h3 style="margin-bottom: 1rem; color: #2d3748;">Escolha um horário:</h3>
        
        @foreach($diasDisponiveis as $dia)
            <div class="day-section">
                <div class="day-header">
                    {{ $dia['dia_semana'] }}, {{ $dia['data_formatada'] }}
                </div>
                
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach($dia['horarios'] as $horario)
                        <div class="time-slot" 
                             data-datetime="{{ $horario['data_hora_inicio'] }}"
                             onclick="selecionarHorario(this)">
                            {{ $horario['inicio'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        
        <div id="dadosCliente" style="display: none; margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">
            <h3 style="margin-bottom: 1rem; color: #2d3748;">Seus dados:</h3>
            
            <div class="form-group">
                <label for="telefone_cliente">Telefone (WhatsApp):</label>
                <input type="tel" id="telefone_cliente" name="telefone_cliente" class="form-control" maxlength="15"
                       placeholder="(11) 99999-9999" required>
            </div>
            
            <div id="nomeClienteGroup" class="form-group" style="display: none;">
                <label for="nome_cliente">Nome completo:</label>
                <input type="text" id="nome_cliente" name="nome_cliente" class="form-control" 
                       placeholder="Digite seu nome completo">
            </div>
            
            <div id="horarioSelecionado" style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                <p style="margin: 0; color: #234e52;"><strong>Horário selecionado:</strong> <span id="horarioTexto"></span></p>
            </div>
            
            <button type="button" class="btn btn-primary" id="btnVerificarTelefone" onclick="verificarTelefone()">
                Verificar Telefone
            </button>
            
            <button type="submit" class="btn btn-success" id="btnConfirmarAgendamento" style="display: none;">
                Confirmar Agendamento
            </button>
            
            <button type="button" class="btn btn-secondary" onclick="cancelarSelecao()">
                Escolher outro horário
            </button>
        </div>
    </form>
@endif
@endsection

@section('scripts')
<script>
let horarioSelecionado = null;

function selecionarHorario(elemento) {
    // Remover seleção anterior
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Selecionar novo horário
    elemento.classList.add('selected');
    horarioSelecionado = elemento.dataset.datetime;
    
    // Atualizar campo hidden
    document.getElementById('dataHoraInicio').value = horarioSelecionado;
    
    // Mostrar formulário de dados
    document.getElementById('dadosCliente').style.display = 'block';
    document.getElementById('btnVerificarTelefone').style.display = 'block';
    document.getElementById('btnConfirmarAgendamento').style.display = 'none';
    document.getElementById('nomeClienteGroup').style.display = 'none';
    document.getElementById('nome_cliente').removeAttribute('required');
    document.getElementById('nome_cliente').removeAttribute('readonly');
    document.getElementById('nome_cliente').value = ''; // Limpar nome ao selecionar novo horário
    
    // Atualizar texto do horário selecionado
    const dataHora = new Date(horarioSelecionado);
    const opcoes = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    document.getElementById('horarioTexto').textContent = dataHora.toLocaleDateString('pt-BR', opcoes);
    
    // Scroll suave para o formulário
    document.getElementById('dadosCliente').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}

async function verificarTelefone() {
    const telefoneInput = document.getElementById('telefone_cliente');
    const telefone = telefoneInput.value.replace(/\D/g, '');
    
    if (!telefone || telefone.length < 10) {
        alert('Por favor, digite um telefone válido com pelo menos 10 dígitos.');
        return;
    }

    // Mostrar loading
    const btnVerificar = document.getElementById('btnVerificarTelefone');
    const textoOriginal = btnVerificar.textContent;
    btnVerificar.textContent = 'Verificando...';
    btnVerificar.disabled = true;

    try {
        console.log('Enviando telefone para verificação:', telefone);
        
        const response = await fetch('/api/check-telefone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ telefone: telefone })
        });

        console.log('Status da resposta:', response.status);
        
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        console.log('Dados recebidos:', data);

        if (data.success === false) {
            throw new Error(data.message || 'Erro na verificação do telefone');
        }

        if (data.exists) {
            // Usuário existente - fazer login automático
            console.log('Usuário existente encontrado:', data.nome);
            document.getElementById('nome_cliente').value = data.nome;
            document.getElementById('nomeClienteGroup').style.display = 'block'; 
            document.getElementById('nome_cliente').setAttribute('readonly', true);
            document.getElementById('btnVerificarTelefone').style.display = 'none';
            document.getElementById('btnConfirmarAgendamento').style.display = 'block';
            
            // Submeter automaticamente após 1 segundo para dar tempo do usuário ver
            setTimeout(() => {
                console.log('Submetendo formulário automaticamente para usuário existente');
                document.getElementById('agendamentoForm').submit();
            }, 1000);
        } else {
            // Novo usuário - pedir nome
            console.log('Novo usuário - solicitando nome');
            document.getElementById('nomeClienteGroup').style.display = 'block';
            document.getElementById('nome_cliente').value = '';
            document.getElementById('nome_cliente').removeAttribute('readonly');
            document.getElementById('nome_cliente').setAttribute('required', true);
            document.getElementById('btnVerificarTelefone').style.display = 'none';
            document.getElementById('btnConfirmarAgendamento').style.display = 'block';
            
            // Focar no campo nome
            document.getElementById('nome_cliente').focus();
        }
    } catch (error) {
        console.error('Erro ao verificar telefone:', error);
        alert('Ocorreu um erro ao verificar o telefone: ' + error.message + '. Tente novamente.');
        
        // Restaurar botão
        btnVerificar.textContent = textoOriginal;
        btnVerificar.disabled = false;
    }
}

function cancelarSelecao() {
    // Remover todas as seleções
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Esconder formulário
    document.getElementById('dadosCliente').style.display = 'none';
    
    // Limpar dados
    horarioSelecionado = null;
    document.getElementById('dataHoraInicio').value = '';
    document.getElementById('nome_cliente').value = '';
    document.getElementById('telefone_cliente').value = '';
    document.getElementById('nomeClienteGroup').style.display = 'none';
    document.getElementById('nome_cliente').removeAttribute('required');
    document.getElementById('nome_cliente').removeAttribute('readonly');
    document.getElementById('btnVerificarTelefone').style.display = 'block';
    document.getElementById('btnConfirmarAgendamento').style.display = 'none';
    
    // Scroll para o topo
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Máscara para telefone
document.getElementById('telefone_cliente')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        if (value.length < 14) {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
    }
    e.target.value = value;
});
</script>
@endsection


