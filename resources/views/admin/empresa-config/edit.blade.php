@extends('admin.layout')

@section('title', 'Configurações da Empresa')
@section('header', 'Configurações - ' . $empresa->nome)

@section('content')
<div class="card">
    <h2>Configurações de Agendamento</h2>
    <p>Configure os parâmetros de agendamento para sua empresa.</p>
    
    <form action="{{ route('admin.empresa-config.update') }}" method="POST">
        @csrf
        @method('PATCH')
        
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
            <small class="form-text">Define o intervalo entre os horários disponíveis para agendamento.</small>
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
            <small class="form-text">Tempo mínimo necessário entre o agendamento e o horário do serviço.</small>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">Salvar Configurações</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Voltar ao Dashboard</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Configurações Atuais</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div style="padding: 1rem; background-color: #f8f9fa; border-radius: 8px;">
            <h4 style="color: #3498db; margin-bottom: 0.5rem;">Tamanho do Slot</h4>
            <p style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">{{ $empresa->tamanho_slot_minutos }} min</p>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Intervalo entre horários</p>
        </div>
        
        <div style="padding: 1rem; background-color: #f8f9fa; border-radius: 8px;">
            <h4 style="color: #27ae60; margin-bottom: 0.5rem;">Antecedência Mínima</h4>
            <p style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">
                @if($empresa->antecedencia_minima_horas == 0)
                    Sem antecedência
                @else
                    {{ $empresa->antecedencia_minima_horas }}h
                @endif
            </p>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Tempo mínimo para agendar</p>
        </div>
    </div>
</div>
@endsection

