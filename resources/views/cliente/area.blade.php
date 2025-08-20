@extends('layout')

@section('title', 'Meus Agendamentos')
@section('header', 'Meus Agendamentos')
@section('subtitle', 'Gerencie seus agendamentos')

@section('content')

@if($agendamentos->isEmpty())
    <div class="alert alert-error">
        <p><strong>Nenhum agendamento encontrado</strong></p>
        <p>Você não possui agendamentos cadastrados com este telefone.</p>
    </div>

    <a href="{{ url('/') }}" class="btn btn-primary">
        Fazer novo agendamento
    </a>
@else
    <div style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
                <p style="color: #718096; margin: 0;">
                    <strong>Cliente:</strong> {{ $cliente->nome }}<br>
                    <strong>Telefone:</strong> {{ $cliente->telefone }}
                </p>
            </div>
            <div>
                <form method="POST" action="{{ route('cliente.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </div>

    @foreach($agendamentos as $agendamento)
        <div class="service-card" style="margin-bottom: 1rem;">
            <div style="display: flex; justify-content: between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="flex: 1;">
                    <div class="service-name">{{ $agendamento->empresa->nome }}</div>
                    <div style="color: #718096; font-size: 0.9rem; margin-bottom: 0.5rem;">
                        {{ $agendamento->servico->nome }}
                    </div>
                </div>

                @php
                    $dataAgendamento = \Carbon\Carbon::parse($agendamento->data_hora_inicio);
                    $agora = \Carbon\Carbon::now();
                    $podeSerCancelado = $dataAgendamento->isFuture() && $agendamento->status === 'confirmado';
                    $jaPassou = $dataAgendamento->isPast();
                @endphp

                <div style="text-align: right;">
                    @if($agendamento->status === 'confirmado')
                        @if($podeSerCancelado)
                            <span style="background: #48bb78; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                Confirmado
                            </span>
                        @elseif($jaPassou)
                            <span style="background: #4299e1; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                Realizado
                            </span>
                        @endif
                    @elseif($agendamento->status === 'cancelado')
                        <span style="background: #f56565; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                            Cancelado
                        </span>
                    @endif
                </div>
            </div>

            <div style="background: #f7fafc; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                    <div>
                        <strong>Data:</strong><br>
                        {{ $dataAgendamento->format('d/m/Y') }}
                    </div>
                    <div>
                        <strong>Horário:</strong><br>
                        {{ $dataAgendamento->format('H:i') }}
                    </div>
                    <div>
                        <strong>Duração:</strong><br>
                        {{ $agendamento->servico->duracao_minutos }} minutos
                    </div>
                    <div>
                        <strong>Status:</strong><br>
                        @if($agendamento->status === 'confirmado')
                            @if($podeSerCancelado)
                                Agendado
                            @elseif($jaPassou)
                                Realizado
                            @endif
                        @else
                            {{ ucfirst($agendamento->status) }}
                        @endif
                    </div>
                </div>
            </div>

            @if($podeSerCancelado)
                <form method="POST" action="{{ route('agendamento.cancelar') }}"
                      onsubmit="return confirm('Tem certeza que deseja cancelar este agendamento?')"
                      style="margin-top: 1rem;">
                    @csrf
                    <input type="hidden" name="agendamento_id" value="{{ $agendamento->id }}">
                    <input type="hidden" name="telefone" value="{{ $cliente->telefone }}">
                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                        Cancelar Agendamento
                    </button>
                </form>
            @endif
        </div>
    @endforeach

    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">
        <a href="{{ url('/') }}" class="btn btn-primary">
            Fazer novo agendamento
        </a>
    </div>
@endif

@endsection

@section('scripts')
<script>
// Máscara para telefone
document.getElementById('telefone_cliente')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        if (value.length < 14) {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
    }
    e.target.value = value;
});
</script>
@endsection

