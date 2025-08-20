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

<div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #e2e8f0;">
    <h3 style="margin-bottom: 1rem; color: #2d3748;">Outras opções</h3>
    <a href="{{ route('agendamento.cancelar') }}" class="btn btn-secondary">
        Cancelar Agendamento
    </a>
</div>
@endsection

