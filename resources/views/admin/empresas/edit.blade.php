@extends('admin.layout')

@section('title', 'Editar Empresa')
@section('header', 'Editar Empresa')

@section('content')
<div class="card">
    <h2>Editar: {{ $empresa->nome }}</h2>
    
    <form action="{{ route('admin.empresas.update', $empresa) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="nome">Nome da Empresa:</label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome', $empresa->nome) }}" required>
        </div>
        
        <div class="form-group">
            <label for="tamanho_slot_minutos">Tamanho do Slot (minutos):</label>
            <select id="tamanho_slot_minutos" name="tamanho_slot_minutos" class="form-control" required>
                <option value="5" {{ old('tamanho_slot_minutos', $empresa->tamanho_slot_minutos) == 5 ? 'selected' : '' }}>5 minutos</option>
                <option value="10" {{ old('tamanho_slot_minutos', $empresa->tamanho_slot_minutos) == 10 ? 'selected' : '' }}>10 minutos</option>
                <option value="15" {{ old('tamanho_slot_minutos', $empresa->tamanho_slot_minutos) == 15 ? 'selected' : '' }}>15 minutos</option>
                <option value="20" {{ old('tamanho_slot_minutos', $empresa->tamanho_slot_minutos) == 20 ? 'selected' : '' }}>20 minutos</option>
                <option value="30" {{ old('tamanho_slot_minutos', $empresa->tamanho_slot_minutos) == 30 ? 'selected' : '' }}>30 minutos</option>
                <option value="60" {{ old('tamanho_slot_minutos', $empresa->tamanho_slot_minutos) == 60 ? 'selected' : '' }}>60 minutos</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="antecedencia_minima_horas">Antecedência Mínima (horas):</label>
            <select id="antecedencia_minima_horas" name="antecedencia_minima_horas" class="form-control" required>
                <option value="0" {{ old('antecedencia_minima_horas', $empresa->antecedencia_minima_horas) == 0 ? 'selected' : '' }}>Sem antecedência</option>
                <option value="1" {{ old('antecedencia_minima_horas', $empresa->antecedencia_minima_horas) == 1 ? 'selected' : '' }}>1 hora</option>
                <option value="2" {{ old('antecedencia_minima_horas', $empresa->antecedencia_minima_horas) == 2 ? 'selected' : '' }}>2 horas</option>
                <option value="4" {{ old('antecedencia_minima_horas', $empresa->antecedencia_minima_horas) == 4 ? 'selected' : '' }}>4 horas</option>
                <option value="12" {{ old('antecedencia_minima_horas', $empresa->antecedencia_minima_horas) == 12 ? 'selected' : '' }}>12 horas</option>
                <option value="24" {{ old('antecedencia_minima_horas', $empresa->antecedencia_minima_horas) == 24 ? 'selected' : '' }}>24 horas</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
            <a href="{{ route('admin.empresas.index') }}" class="btn btn-primary">Cancelar</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Informações da Empresa</h3>
    <p><strong>Slug atual:</strong> {{ $empresa->slug }}</p>
    <p><strong>Status:</strong> {{ $empresa->ativo ? 'Ativa' : 'Inativa' }}</p>
    <p><strong>URL pública:</strong> <a href="{{ route('empresa', $empresa->slug) }}" target="_blank">{{ url('/' . $empresa->slug) }}</a></p>
    <p><strong>Criada em:</strong> {{ $empresa->created_at->format('d/m/Y H:i') }}</p>
</div>
@endsection

