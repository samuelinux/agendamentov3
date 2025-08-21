@extends('admin.layout')

@section('title', 'Carga Horária Semanal')
@section('header', 'Carga Horária Semanal - ' . $empresa->nome)

@section('content')
<div class="card">
    <h2>Definir Carga Horária Semanal</h2>
    <p>Configure os horários de funcionamento da sua empresa para cada dia da semana.</p>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <form method="POST" action="{{ route('admin.working_hours.save') }}">
        @csrf
        
        <div class="working-hours-container">
            @foreach($diasSemana as $diaSemana => $nomeDia)
                <div class="day-row" style="display: flex; align-items: center; margin-bottom: 1rem; padding: 1rem; border: 1px solid #ddd; border-radius: 5px;">
                    <div class="day-name" style="width: 150px; font-weight: bold;">
                        {{ $nomeDia }}
                    </div>
                    
                    <div class="time-inputs" style="display: flex; gap: 1rem; align-items: center;">
                        <div>
                            <label for="hora_inicio_{{ $diaSemana }}" style="display: block; font-size: 0.9rem; margin-bottom: 0.25rem;">Hora Início:</label>
                            <input 
                                type="time" 
                                id="hora_inicio_{{ $diaSemana }}" 
                                name="jornadas[{{ $diaSemana }}][hora_inicio]" 
                                value="{{ $jornadas->get($diaSemana)?->hora_inicio ?? '' }}"
                                class="form-control"
                                style="width: 120px;"
                            >
                        </div>
                        
                        <div>
                            <label for="hora_fim_{{ $diaSemana }}" style="display: block; font-size: 0.9rem; margin-bottom: 0.25rem;">Hora Fim:</label>
                            <input 
                                type="time" 
                                id="hora_fim_{{ $diaSemana }}" 
                                name="jornadas[{{ $diaSemana }}][hora_fim]" 
                                value="{{ $jornadas->get($diaSemana)?->hora_fim ?? '' }}"
                                class="form-control"
                                style="width: 120px;"
                            >
                        </div>
                        
                        <input type="hidden" name="jornadas[{{ $diaSemana }}][dia_semana]" value="{{ $diaSemana }}">
                        
                        <div style="margin-left: 1rem;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearDay({{ $diaSemana }})">
                                Limpar
                            </button>
                        </div>
                    </div>
                </div>
                
                @error("jornadas.{$diaSemana}.hora_inicio")
                    <div class="text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
                @error("jornadas.{$diaSemana}.hora_fim")
                    <div class="text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            @endforeach
        </div>
        
        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                Salvar Carga Horária
            </button>
            
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                Voltar ao Dashboard
            </a>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3>Instruções</h3>
    <ul style="margin-left: 2rem;">
        <li>Para cada dia da semana, defina a hora de início e fim do funcionamento.</li>
        <li>Deixe os campos vazios para dias em que a empresa não funciona.</li>
        <li>Use o botão "Limpar" para remover os horários de um dia específico.</li>
        <li>A hora de fim deve ser posterior à hora de início.</li>
    </ul>
</div>

<script>
function clearDay(diaSemana) {
    document.getElementById('hora_inicio_' + diaSemana).value = '';
    document.getElementById('hora_fim_' + diaSemana).value = '';
}

// Validação no frontend
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        let hasError = false;
        
        // Limpar mensagens de erro anteriores
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        // Validar cada dia
        @foreach($diasSemana as $diaSemana => $nomeDia)
            const horaInicio{{ $diaSemana }} = document.getElementById('hora_inicio_{{ $diaSemana }}');
            const horaFim{{ $diaSemana }} = document.getElementById('hora_fim_{{ $diaSemana }}');
            
            if (horaInicio{{ $diaSemana }}.value && horaFim{{ $diaSemana }}.value) {
                if (horaInicio{{ $diaSemana }}.value >= horaFim{{ $diaSemana }}.value) {
                    showError(horaFim{{ $diaSemana }}, 'A hora de fim deve ser posterior à hora de início.');
                    hasError = true;
                }
            } else if (horaInicio{{ $diaSemana }}.value || horaFim{{ $diaSemana }}.value) {
                if (!horaInicio{{ $diaSemana }}.value) {
                    showError(horaInicio{{ $diaSemana }}, 'Informe a hora de início.');
                    hasError = true;
                }
                if (!horaFim{{ $diaSemana }}.value) {
                    showError(horaFim{{ $diaSemana }}, 'Informe a hora de fim.');
                    hasError = true;
                }
            }
        @endforeach
        
        if (hasError) {
            e.preventDefault();
        }
    });
    
    function showError(element, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-danger';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;
        element.parentNode.appendChild(errorDiv);
    }
});
</script>
@endsection

