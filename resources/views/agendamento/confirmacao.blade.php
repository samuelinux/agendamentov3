@extends('layout')

@section('title', 'Agendamento Confirmado')
@section('header', 'Agendamento Confirmado!')
@section('subtitle', 'Seu agendamento foi realizado com sucesso')

@section('content')
<div style="text-align: center; margin-bottom: 2rem;">
    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
        <span style="color: white; font-size: 2rem;">✓</span>
    </div>
</div>

<div style="background: #f0fff4; border: 2px solid #9ae6b4; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
    <h3 style="margin: 0 0 1rem 0; color: #22543d;">Detalhes do Agendamento</h3>
    
    <div style="margin-bottom: 1rem;">
        <strong style="color: #22543d;">Empresa:</strong><br>
        <span style="color: #2f855a;">{{ $empresa->nome }}</span>
    </div>
    
    <div style="margin-bottom: 1rem;">
        <strong style="color: #22543d;">Serviço:</strong><br>
        <span style="color: #2f855a;">{{ $servico->nome }}</span>
    </div>
    
    <div style="margin-bottom: 1rem;">
        <strong style="color: #22543d;">Cliente:</strong><br>
        <span style="color: #2f855a;">{{ $cliente->nome }}</span>
    </div>
    
    <div style="margin-bottom: 1rem;">
        <strong style="color: #22543d;">Telefone:</strong><br>
        <span style="color: #2f855a;">{{ $cliente->telefone }}</span>
    </div>
    
    <div style="margin-bottom: 1rem;">
        <strong style="color: #22543d;">Data e Horário:</strong><br>
        <span style="color: #2f855a; font-size: 1.1rem; font-weight: 600;">
            {{ \Carbon\Carbon::parse($agendamento->data_hora_inicio)->format('d/m/Y \à\s H:i') }}
        </span>
    </div>
    
    <div>
        <strong style="color: #22543d;">Duração:</strong><br>
        <span style="color: #2f855a;">{{ $servico->duracao_minutos }} minutos</span>
    </div>
</div>

<div style="background: #ebf8ff; border: 2px solid #90cdf4; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
    <h4 style="margin: 0 0 1rem 0; color: #2a4365;">📱 Importante</h4>
    <p style="margin: 0 0 0.5rem 0; color: #2c5282;">
        <strong>Você receberá uma confirmação via WhatsApp</strong> no número {{ $cliente->telefone }}.
    </p>
    <p style="margin: 0; color: #2c5282;">
        Guarde o <strong>código do agendamento: #{{ $agendamento->id }}</strong> para eventuais cancelamentos.
    </p>
</div>

<div style="margin-bottom: 1rem;">
    <a href="{{ route('empresa', $empresa->slug) }}" class="btn btn-primary">
        Fazer Novo Agendamento
    </a>
</div>

<div style="margin-bottom: 1rem;">
    <a href="{{ route('agendamento.cancelar') }}" class="btn btn-secondary">
        Cancelar Este Agendamento
    </a>
</div>

<div>
    <a href="{{ route('home') }}" class="btn btn-secondary">
        Voltar ao Início
    </a>
</div>

<div style="margin-top: 2rem; padding: 1rem; background: #f7fafc; border-radius: 8px; text-align: center;">
    <p style="margin: 0; color: #718096; font-size: 0.9rem;">
        <strong>Precisa cancelar?</strong><br>
        Use o código <strong>#{{ $agendamento->id }}</strong> e seu telefone {{ $cliente->telefone }}
    </p>
</div>
@endsection

