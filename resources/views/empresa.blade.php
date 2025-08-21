@extends('layout')

@section('title', $empresa->nome)
@section('header', $empresa->nome)
@section('subtitle', 'Escolha o serviço que deseja agendar')

@section('content')
<a href="{{ route('home') }}" class="back-link">
    ← Voltar para início
</a>

@if($empresa->servicos->where('ativo', true)->isEmpty())
    <div class="alert alert-error">
        <p><strong>Nenhum serviço disponível no momento.</strong></p>
        <p>Esta empresa não possui serviços ativos para agendamento.</p>
    </div>
@else
    @foreach($empresa->servicos->where('ativo', true) as $servico)
        <a href="{{ route('agendamento.horarios', [$empresa, $servico]) }}" class="service-card-compact">
            <div class="service-info">
                <div class="service-name-compact">{{ $servico->nome }}</div>
                <div class="service-details">
                    <span class="service-price">R$ {{ number_format($servico->valor ?? 0, 2, ',', '.') }}</span>
                    <span class="service-separator">•</span>
                    <span class="service-duration">{{ $servico->duracao_minutos }} min</span>
                </div>
            </div>
        </a>
    @endforeach
@endif

<!-- Botão flutuante "Cancelar Agendamento" -->
<div class="floating-button">
    <a href="{{ route('agendamento.cancelar') }}" class="btn-floating">
        <span class="btn-floating-icon">✕</span>
        <span class="btn-floating-text">Cancelar Agendamento</span>
    </a>
</div>

<style>
.floating-button {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    width: calc(100% - 40px);
    max-width: 400px;
}

.btn-floating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 16px 24px;
    background: linear-gradient(135deg, #e53e3e, #c53030);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    box-shadow: 0 8px 25px rgba(229, 62, 62, 0.3);
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-floating:hover {
    background: linear-gradient(135deg, #c53030, #9c2626);
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(229, 62, 62, 0.4);
    color: white;
    text-decoration: none;
}

.btn-floating:active {
    transform: translateY(0);
    box-shadow: 0 6px 20px rgba(229, 62, 62, 0.3);
}

.btn-floating-icon {
    font-size: 18px;
    font-weight: bold;
}

.btn-floating-text {
    font-size: 16px;
}

/* Responsividade */
@media (max-width: 480px) {
    .floating-button {
        bottom: 15px;
        width: calc(100% - 30px);
    }
    
    .btn-floating {
        padding: 14px 20px;
        font-size: 15px;
    }
    
    .btn-floating-icon {
        font-size: 16px;
    }
    
    .btn-floating-text {
        font-size: 15px;
    }
}

/* Garantir espaço para o botão flutuante */
body {
    padding-bottom: 100px;
}
</style>
@endsection

