@extends('admin.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard - ' . $empresa->nome)

@section('content')
<div class="card">
    <h2>Bem-vindo, {{ auth()->user()->nome }}!</h2>
    <p>Gerencie sua empresa <strong>{{ $empresa->nome }}</strong> através do painel administrativo.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
    <div class="card">
        <h3 style="color: #3498db; margin-bottom: 0.5rem;">Agendamentos Hoje</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #2c3e50;">{{ $agendamentosHoje }}</p>
        <p style="color: #7f8c8d;">agendamentos confirmados</p>
    </div>
    
    <div class="card">
        <h3 style="color: #27ae60; margin-bottom: 0.5rem;">Configurações</h3>
        <p><strong>Slots:</strong> {{ $empresa->tamanho_slot_minutos }} minutos</p>
        <p><strong>Antecedência:</strong> {{ $empresa->antecedencia_minima_horas }}h</p>
        <p><strong>URL:</strong> <a href="{{ route('empresa', $empresa->slug) }}" target="_blank">{{ $empresa->slug }}</a></p>
    </div>
    
    <div class="card">
        <h3 style="color: #e74c3c; margin-bottom: 0.5rem;">Ações Rápidas</h3>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <a href="{{ route('admin.servicos.index') }}" class="btn btn-primary">Gerenciar Serviços</a>
            <a href="{{ route('admin.empresa-config.edit') }}" class="btn btn-warning">Configurações da Empresa</a>
            <a href="{{ route('admin.relatorios.atendimentos') }}" class="btn btn-info">Relatórios de Atendimentos</a>
            <a href="{{ route('empresa', $empresa->slug) }}" target="_blank" class="btn btn-success">Ver Site Público</a>
        </div>
    </div>
</div>

<div class="card">
    <h2>Próximos Passos</h2>
    <p>Para começar a receber agendamentos, certifique-se de que:</p>
    <ul style="margin-left: 2rem; margin-top: 1rem;">
        <li>Seus serviços estão cadastrados e ativos</li>
        <li>Os horários de funcionamento estão configurados</li>
        <li>Compartilhe o link da sua empresa: <strong>{{ url('/' . $empresa->slug) }}</strong></li>
    </ul>
</div>
@endsection

