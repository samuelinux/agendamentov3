@extends('admin.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard - ' . $empresa->nome)

@section('content')
<div class="card">
    <h2>Bem-vindo, {{ auth()->user()->nome }}!</h2>
    <p>Gerencie sua empresa <strong>{{ $empresa->nome }}</strong> através do painel administrativo.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="background-color: #e8f5e9; border-left: 5px solid #4CAF50;">
        <h3 style="color: #4CAF50; margin-bottom: 0.5rem;">Valor Ganho Hoje</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #2e7d32;">R$ {{ number_format($valorGanho, 2, ',', '.') }}</p>
        <p style="color: #66bb6a;">agendamentos realizados</p>
    </div>
    
    <div class="card" style="background-color: #e3f2fd; border-left: 5px solid #2196F3;">
        <h3 style="color: #2196F3; margin-bottom: 0.5rem;">Valor Futuro Hoje</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #1976d2;">R$ {{ number_format($valorFuturo, 2, ',', '.') }}</p>
        <p style="color: #64b5f6;">agendamentos a realizar</p>
    </div>
    
    <div class="card" style="background-color: #fff3e0; border-left: 5px solid #FF9800;">
        <h3 style="color: #FF9800; margin-bottom: 0.5rem;">Agendamentos Hoje</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #f57c00;">{{ $agendamentosDoDia->total() }}</p>
        <p style="color: #ffb74d;">total de agendamentos</p>
    </div>
</div>

<div class="card">
    <h2>Agendamentos do Dia ({{ \Carbon\Carbon::today()->format('d/m/Y') }})</h2>
    
    @if($agendamentosDoDia->isEmpty())
        <p>Nenhum agendamento para hoje.</p>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agendamentosDoDia as $agendamento)
                        <tr class="{{ $agendamento->status === 'realizado' ? 'table-success' : '' }}">
                            <td>{{ \Carbon\Carbon::parse($agendamento->data_hora_inicio)->format('H:i') }}</td>
                            <td>{{ $agendamento->usuario->nome }}</td>
                            <td>{{ $agendamento->servico->nome }}</td>
                            <td>R$ {{ number_format($agendamento->servico->valor, 2, ',', '.') }}</td>
                            <td>
                                @if($agendamento->status === 'realizado')
                                    <span class="badge badge-success">Realizado</span>
                                @else
                                    <span class="badge badge-info">Agendado</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div class="per-page-selector">
                <label for="perPage">Itens por página:</label>
                <select id="perPage" onchange="window.location.href = '?per_page=' + this.value" class="form-control">
                    @foreach([10, 25, 50, 100] as $option)
                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pagination-links">
                {{ $agendamentosDoDia->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>

<div class="card">
    <h2>Próximos Passos</h2>
    <p>Para começar a receber agendamentos, certifique-se de que:</p>
    <ul style="margin-left: 2rem; margin-top: 1rem;">
        <li>Seus serviços estão cadastrados e ativos</li>
        <li>Os horários de funcionamento estão configurados</li>
        <li>Compartilhe o link da sua empresa: <strong>{{ url('/' . $empresa->slug) }}</strong></li>
    </ul>
    
    <div style="margin-top: 2rem;">
        <a href="{{ route('admin.working_hours.form') }}" class="btn btn-primary">
            Configurar Carga Horária Semanal
        </a>
    </div>
</div>
@endsection


