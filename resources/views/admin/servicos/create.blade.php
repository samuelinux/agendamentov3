@extends('admin.layout')

@section('title', 'Novo Serviço')
@section('header', 'Novo Serviço')

@section('content')
<div class="card">
    <h2>Cadastrar Novo Serviço</h2>
    
    <form action="{{ route('admin.servicos.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="nome">Nome do Serviço:</label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome') }}" required placeholder="Ex: Corte de Cabelo">
        </div>
        
        <div class="form-group">
            <label for="descricao">Descrição (opcional):</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="3" placeholder="Descreva brevemente o serviço...">{{ old('descricao') }}</textarea>
        </div>
        
        <div class="form-group">
            <label for="duracao_minutos">Duração (minutos):</label>
            <input type="number" id="duracao_minutos" name="duracao_minutos" class="form-control" value="{{ old('duracao_minutos', 30) }}" min="5" max="480" required>
            <small style="color: #7f8c8d;">Tempo necessário para realizar o serviço (de 5 a 480 minutos)</small>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">Criar Serviço</button>
            <a href="{{ route('admin.servicos.index') }}" class="btn btn-primary">Cancelar</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Dicas para Cadastrar Serviços</h3>
    <ul style="margin-left: 2rem;">
        <li><strong>Nome:</strong> Use nomes claros e descritivos que os clientes entendam facilmente</li>
        <li><strong>Descrição:</strong> Adicione detalhes que ajudem o cliente a escolher o serviço certo</li>
        <li><strong>Duração:</strong> Considere o tempo real necessário, incluindo preparação e limpeza</li>
        <li><strong>Slots:</strong> A duração deve ser compatível com o tamanho dos slots da empresa ({{ auth()->user()->empresa->tamanho_slot_minutos }} minutos)</li>
    </ul>
</div>
@endsection

