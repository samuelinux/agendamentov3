@extends('layout')

@section('title', 'Sistema de Agendamento')
@section('header', 'Sistema de Agendamento')
@section('subtitle', 'Escolha uma empresa para agendar seus serviços')

@section('content')
@if($empresas->isEmpty())
    <div class="alert alert-error">
        <p><strong>Nenhuma empresa disponível</strong></p>
        <p>Não há empresas cadastradas no sistema no momento.</p>
    </div>
@else
    
@endif


@endsection

