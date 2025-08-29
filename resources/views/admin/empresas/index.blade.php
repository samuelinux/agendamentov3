@extends('admin.layout')

@section('title', 'Gestão de Empresas')
@section('header', 'Gestão de Empresas')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Empresas Cadastradas</h2>
        <a href="{{ route('admin.empresas.create') }}" class="btn btn-primary">Nova Empresa</a>
    </div>
    
    @if($empresas->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Slots</th>
                    <th>Antecedência</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($empresas as $empresa)
                    <tr>
                        <td>{{ $empresa->nome }}</td>
                        <td>
                            <a href="{{ route('empresa', $empresa->slug) }}" target="_blank">
                                {{ $empresa->slug }}
                            </a>
                        </td>
                        <td>{{ $empresa->tamanho_slot_minutos }}min</td>
                        <td>{{ $empresa->antecedencia_minima_horas }}h</td>
                        <td>
                            @if($empresa->ativo)
                                <span class="badge badge-success">Ativa</span>
                            @else
                                <span class="badge badge-danger">Inativa</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('admin.empresas.edit', $empresa) }}" class="btn btn-warning btn-sm">Editar</a>
                                
                                <form action="{{ route('admin.empresas.toggle-status', $empresa) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn {{ $empresa->ativo ? 'btn-danger' : 'btn-success' }} btn-sm">
                                        {{ $empresa->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhuma empresa cadastrada ainda.</p>
    @endif
</div>
@endsection

