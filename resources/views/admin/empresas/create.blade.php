@extends('admin.layout')

@section('title', 'Nova Empresa')
@section('header', 'Nova Empresa')

@section('content')
<div class="card">
    <h2>Cadastrar Nova Empresa</h2>
    
    <form action="{{ route('admin.empresas.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="nome">Nome da Empresa:</label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome') }}" required>
        </div>
        
        <div class="form-group">
            <label for="tamanho_slot_minutos">Tamanho do Slot (minutos):</label>
            <select id="tamanho_slot_minutos" name="tamanho_slot_minutos" class="form-control" required>
                <option value="5" {{ old('tamanho_slot_minutos') == 5 ? 'selected' : '' }}>5 minutos</option>
                <option value="10" {{ old('tamanho_slot_minutos') == 10 ? 'selected' : '' }}>10 minutos</option>
                <option value="15" {{ old('tamanho_slot_minutos', 15) == 15 ? 'selected' : '' }}>15 minutos</option>
                <option value="20" {{ old('tamanho_slot_minutos') == 20 ? 'selected' : '' }}>20 minutos</option>
                <option value="30" {{ old('tamanho_slot_minutos') == 30 ? 'selected' : '' }}>30 minutos</option>
                <option value="60" {{ old('tamanho_slot_minutos') == 60 ? 'selected' : '' }}>60 minutos</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="antecedencia_minima_horas">Antecedência Mínima (horas):</label>
            <select id="antecedencia_minima_horas" name="antecedencia_minima_horas" class="form-control" required>
                <option value="0" {{ old('antecedencia_minima_horas') == 0 ? 'selected' : '' }}>Sem antecedência</option>
                <option value="1" {{ old('antecedencia_minima_horas', 1) == 1 ? 'selected' : '' }}>1 hora</option>
                <option value="2" {{ old('antecedencia_minima_horas') == 2 ? 'selected' : '' }}>2 horas</option>
                <option value="4" {{ old('antecedencia_minima_horas') == 4 ? 'selected' : '' }}>4 horas</option>
                <option value="12" {{ old('antecedencia_minima_horas') == 12 ? 'selected' : '' }}>12 horas</option>
                <option value="24" {{ old('antecedencia_minima_horas') == 24 ? 'selected' : '' }}>24 horas</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">Criar Empresa</button>
            <a href="{{ route('admin.empresas.index') }}" class="btn btn-primary">Cancelar</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Informações Importantes</h3>
    <ul style="margin-left: 2rem;">
        <li><strong>Slug:</strong> Será gerado automaticamente baseado no nome da empresa</li>
        <li><strong>Tamanho do Slot:</strong> Define a granularidade dos horários de agendamento</li>
        <li><strong>Antecedência Mínima:</strong> Tempo mínimo necessário entre o agendamento e o serviço</li>
    </ul>
</div>
@endsection

