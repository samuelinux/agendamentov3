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
        <div class="service-card">
            <div class="service-name">{{ $servico->nome }}</div>
            
            @if($servico->descricao)
                <p style="color: #718096; margin-bottom: 1rem;">{{ $servico->descricao }}</p>
            @endif
            
            <div class="service-duration">
                <strong>Duração:</strong> {{ $servico->duracao_minutos }} minutos
            </div>
            
            <a href="{{ route('agendamento.horarios', [$empresa, $servico]) }}" class="btn btn-primary">
                Agendar {{ $servico->nome }}
            </a>
        </div>
    @endforeach
@endif

<div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #e2e8f0;">
    <h3 style="margin-bottom: 1rem; color: #2d3748;">Outras opções</h3>
    <a href="{{ route('agendamento.cancelar') }}" class="btn btn-secondary">
        Cancelar Agendamento
    </a>
</div>
@endsection

