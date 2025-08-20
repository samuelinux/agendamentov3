@extends('admin.layout')

@section('title', 'Relatórios de Atendimentos')
@section('header', 'Relatórios - ' . $empresa->nome)

@section('content')
<style>
/* Mobile-first CSS */
.mobile-filters {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.filter-row {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.filter-group label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.filter-input {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.875rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.ganhos-card {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    text-align: center;
}

.ganhos-valor {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.ganhos-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.atendimento-dia {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    overflow: hidden;
}

.dia-header {
    background: #f3f4f6;
    padding: 0.75rem;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.atendimento-item {
    padding: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background-color 0.2s;
}

.atendimento-item:last-child {
    border-bottom: none;
}

.atendimento-item:hover {
    background: #f9fafb;
}

.atendimento-resumo {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.horario-nome {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.horario {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.nome-cliente {
    font-size: 0.875rem;
    color: #6b7280;
}

.expandir-icon {
    color: #9ca3af;
    font-size: 0.75rem;
}

.atendimento-detalhes {
    display: none;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
    margin-top: 0.75rem;
}

.atendimento-detalhes.show {
    display: block;
}

.detalhe-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.detalhe-label {
    color: #6b7280;
    font-weight: 500;
}

.detalhe-valor {
    color: #1f2937;
    font-weight: 600;
}

.pagination-controls {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.per-page-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.pagination-links {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.pagination-links a,
.pagination-links span {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.875rem;
    text-decoration: none;
    color: #374151;
}

.pagination-links .active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

/* Tablet e Desktop */
@media (min-width: 768px) {
    .filter-row {
        flex-direction: row;
        align-items: end;
    }
    
    .filter-group {
        flex: 1;
    }
    
    .pagination-controls {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}
</style>

<div class="card">
    <h2>Relatórios de Atendimentos</h2>
    
    <!-- Filtros -->
    <form method="GET" class="mobile-filters">
        <div class="filter-row">
            <div class="filter-group">
                <label for="data_inicio">Data/Hora Início:</label>
                <input type="datetime-local" id="data_inicio" name="data_inicio" value="{{ $dataInicio }}" class="filter-input">
            </div>
            
            <div class="filter-group">
                <label for="data_fim">Data/Hora Fim:</label>
                <input type="datetime-local" id="data_fim" name="data_fim" value="{{ $dataFim }}" class="filter-input">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('admin.relatorios.atendimentos') }}" class="btn btn-secondary">Limpar</a>
        </div>
    </form>
    
    <!-- Ganhos Totais -->
    <div class="ganhos-card">
        <div class="ganhos-valor">R$ {{ number_format($ganhosTotais, 2, ',', '.') }}</div>
        <div class="ganhos-label">Ganhos no Período</div>
    </div>
    
    <!-- Lista de Atendimentos -->
    @if($agendamentosPorData->count() > 0)
        @foreach($agendamentosPorData as $data => $agendamentosData)
            <div class="atendimento-dia">
                <div class="dia-header">
                    {{ \Carbon\Carbon::parse($data)->format('d/m/Y - l') }}
                </div>
                
                @foreach($agendamentosData as $agendamento)
                    <div class="atendimento-item" onclick="toggleDetalhes({{ $agendamento->id }})">
                        <div class="atendimento-resumo">
                            <div class="horario-nome">
                                <div class="horario">{{ $agendamento->data_hora_inicio->format('H:i') }}</div>
                                <div class="nome-cliente">{{ $agendamento->usuario->nome }}</div>
                            </div>
                            <div class="expandir-icon">▼</div>
                        </div>
                        
                        <div class="atendimento-detalhes" id="detalhes-{{ $agendamento->id }}">
                            <div class="detalhe-row">
                                <span class="detalhe-label">Telefone:</span>
                                <span class="detalhe-valor">{{ $agendamento->usuario->telefone }}</span>
                            </div>
                            <div class="detalhe-row">
                                <span class="detalhe-label">Serviço:</span>
                                <span class="detalhe-valor">{{ $agendamento->servico->nome }}</span>
                            </div>
                            <div class="detalhe-row">
                                <span class="detalhe-label">Valor:</span>
                                <span class="detalhe-valor">R$ {{ number_format($agendamento->servico->valor, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
        
        <!-- Controles de Paginação -->
        <div class="pagination-controls">
            <form method="GET" class="per-page-selector">
                <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                <input type="hidden" name="data_fim" value="{{ $dataFim }}">
                <label for="per_page">Itens por página:</label>
                <select name="per_page" id="per_page" onchange="this.form.submit()" class="filter-input" style="width: auto;">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </form>
            
            <div class="pagination-links">
                {{ $agendamentos->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <p>Nenhum atendimento encontrado no período selecionado.</p>
        </div>
    @endif
</div>

<script>
function toggleDetalhes(agendamentoId) {
    const detalhes = document.getElementById('detalhes-' + agendamentoId);
    const icon = detalhes.parentElement.querySelector('.expandir-icon');
    
    if (detalhes.classList.contains('show')) {
        detalhes.classList.remove('show');
        icon.textContent = '▼';
    } else {
        detalhes.classList.add('show');
        icon.textContent = '▲';
    }
}
</script>
@endsection

