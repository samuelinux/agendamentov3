@extends('admin.layout')

@section('title', 'Usuários')
@section('header', 'Lista de Usuários')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Usuários Cadastrados</h2>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-success">+ Novo Usuário</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-top:1rem;">
            {{ session('success') }}
        </div>
    @endif

    <table class="table" style="margin-top:1rem;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Tipo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>{{ $usuario->nome }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->telefone }}</td>
                    <td>{{ ucfirst($usuario->tipo) }}</td>
                    <td style="display:flex; gap:.5rem;">
                        <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-primary btn-sm">Editar</a>
                        <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Nenhum usuário encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $usuarios->links() }}
</div>
@endsection
