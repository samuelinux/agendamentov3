@extends('admin.layout')

@section('title', 'Gestão de Serviços')
@section('header', 'Gestão de Serviços')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Serviços da {{ auth()->user()->empresa->nome }}</h2>
        <a href="{{ route('admin.servicos.create') }}" class="btn btn-primary">Novo Serviço</a>
    </div>
    
    @if($servicos->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <!--<th>Duração</th> -->
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($servicos as $servico)
                    <tr>
                        <td>{{ $servico->nome }}</td>
                        <td>{{ Str::limit($servico->descricao, 50) ?: '-' }}</td>
                        <!--<td>{{ $servico->duracao_minutos }} min</td>-->
                        <td>R$ {{ number_format($servico->valor, 2, ',', '.') }}</td>
                        <td>
                            @if($servico->ativo)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-danger">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="{{ route('admin.servicos.edit', $servico) }}" class="btn btn-warning btn-sm">Editar</a>
                                
                                <form action="{{ route('admin.servicos.toggle-status', $servico) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn {{ $servico->ativo ? 'btn-danger' : 'btn-success' }} btn-sm">
                                        {{ $servico->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.servicos.destroy', $servico) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este serviço?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 2rem;">
            <p>Nenhum serviço cadastrado ainda.</p>
            <p>Comece criando seu primeiro serviço para que os clientes possam fazer agendamentos.</p>
            <a href="{{ route('admin.servicos.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Criar Primeiro Serviço</a>
        </div>
    @endif
</div>

@if($servicos->count() > 0)
<div class="card">
    <h3>Dica</h3>
    <p>Os clientes podem ver e agendar apenas os serviços que estão <strong>ativos</strong>. Use o botão "Desativar" para temporariamente remover um serviço da lista pública sem excluí-lo.</p>
</div>
@endif
@endsection

