@extends('admin.layout')

@section('title', 'Novo Usuário')
@section('header', 'Cadastrar Novo Usuário')

@section('content')
<div class="card">
    <form action="{{ route('admin.usuarios.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome') }}" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" class="form-control" value="{{ old('telefone') }}">
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar Senha:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="tipo">Tipo:</label>
            <select id="tipo" name="tipo" class="form-control" required>
                <option value="admin" {{ old('tipo') == 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="cliente" {{ old('tipo') == 'cliente' ? 'selected' : '' }}>Cliente</option>
            </select>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-primary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
