@extends('admin.layout')

@section('title', 'Editar Serviço')
@section('header', 'Editar Serviço')

@section('content')
<div class="card">
    <h2>Editar: {{ $servico->nome }}</h2>
    
    <form action="{{ route('admin.servicos.update', $servico) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="nome">Nome do Serviço:</label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome', $servico->nome) }}" required>
        </div>
        
        <div class="form-group">
            <label for="descricao">Descrição (opcional):</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="3" placeholder="Descreva brevemente o serviço...">{{ old('descricao', $servico->descricao) }}</textarea>
        </div>
        
        <div class="form-group">
            <label for="duracao_minutos">Duração (minutos):</label>
            <input type="number" id="duracao_minutos" name="duracao_minutos" class="form-control" value="{{ old('duracao_minutos', $servico->duracao_minutos) }}" min="5" max="480" required>
            <small style="color: #7f8c8d;">Tempo necessário para realizar o serviço (de 5 a 480 minutos)</small>
        </div>
        
        <div class="form-group">
            <label for="valor">Valor (R$):</label>
            <input type="number" id="valor" name="valor" class="form-control" value="{{ old('valor', $servico->valor) }}" min="0" max="9999.99" step="0.01" required>
            <small style="color: #7f8c8d;">Preço do serviço em reais</small>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
            <a href="{{ route('admin.servicos.index') }}" class="btn btn-primary">Cancelar</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Informações do Serviço</h3>
    <p><strong>Status:</strong> {{ $servico->ativo ? 'Ativo' : 'Inativo' }}</p>
    <p><strong>Criado em:</strong> {{ $servico->created_at->format('d/m/Y H:i') }}</p>
    @if($servico->updated_at != $servico->created_at)
        <p><strong>Última atualização:</strong> {{ $servico->updated_at->format('d/m/Y H:i') }}</p>
    @endif
    
    @if($servico->agendamentos()->confirmados()->count() > 0)
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 1rem; margin-top: 1rem;">
            <strong>Atenção:</strong> Este serviço possui {{ $servico->agendamentos()->confirmados()->count() }} agendamento(s) confirmado(s). 
            Alterações na duração podem afetar a disponibilidade de horários futuros.
        </div>
    @endif
</div>
@endsection

