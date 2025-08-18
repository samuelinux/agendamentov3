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
    @foreach($empresas as $empresa)
        <div class="service-card">
            <div class="service-name">{{ $empresa->nome }}</div>
            
            @if($empresa->servicos->where('ativo', true)->count() > 0)
                <div style="color: #718096; margin-bottom: 1rem;">
                    <strong>Serviços disponíveis:</strong> {{ $empresa->servicos->where('ativo', true)->count() }}
                </div>
                
                <div style="margin-bottom: 1rem;">
                    @foreach($empresa->servicos->where('ativo', true)->take(3) as $servico)
                        <span style="display: inline-block; background: #e2e8f0; color: #4a5568; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin: 0.25rem 0.25rem 0.25rem 0;">
                            {{ $servico->nome }}
                        </span>
                    @endforeach
                    
                    @if($empresa->servicos->where('ativo', true)->count() > 3)
                        <span style="color: #718096; font-size: 0.9rem;">
                            e mais {{ $empresa->servicos->where('ativo', true)->count() - 3 }} serviço(s)
                        </span>
                    @endif
                </div>
                
                <a href="{{ route('empresa', $empresa->slug) }}" class="btn btn-primary">
                    Ver Serviços e Agendar
                </a>
            @else
                <div style="color: #e53e3e; margin-bottom: 1rem;">
                    Nenhum serviço disponível no momento
                </div>
                
                <button class="btn btn-secondary" disabled>
                    Indisponível
                </button>
            @endif
        </div>
    @endforeach
@endif

<div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #e2e8f0;">
    <h3 style="margin-bottom: 1rem; color: #2d3748;">Outras opções</h3>
    <a href="{{ route('agendamento.cancelar') }}" class="btn btn-secondary">
        Cancelar Agendamento
    </a>
</div>
@endsection

