@extends('admin.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard - ' . $empresa->nome)

@section('content')

    <div class="card">
        <h2>Agendamentos do Dia ({{ \Carbon\Carbon::today()->format('d/m/Y') }})</h2>

        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($agendamentosDoDia->isEmpty())
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
                            <th>Valor Pago</th> {{-- novo --}}
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agendamentosDoDia as $agendamento)
                            @php
                                $hora = \Carbon\Carbon::parse($agendamento->data_hora_inicio)->format('H:i');
                                $valorServico = number_format($agendamento->servico->valor, 2, ',', '.');
                                $valorPago =
                                    $agendamento->valor_pago !== null
                                        ? number_format($agendamento->valor_pago, 2, ',', '.')
                                        : '—';
                            @endphp
                            <tr class="clickable-row {{ $agendamento->status === 'realizado' ? 'table-success' : '' }}"
                                role="button" tabindex="0" data-id="{{ $agendamento->id }}"
                                data-cliente="{{ $agendamento->usuario->nome }}"
                                data-servico="{{ $agendamento->servico->nome }}" data-horario="{{ $hora }}"
                                data-valor-servico="{{ $valorServico }}" data-valor-pago="{{ $agendamento->valor_pago }}"
                                data-action="{{ route('admin.agendamentos.registrar-pagamento', $agendamento) }}"
                                aria-label="Registrar pagamento para {{ $agendamento->usuario->nome }} às {{ $hora }}">

                                <td>{{ $hora }}</td>
                                <td>{{ $agendamento->usuario->nome }}</td>
                                <td>{{ $agendamento->servico->nome }}</td>
                                <td>R$ {{ $valorServico }}</td>
                                <td>{{ $valorPago }}</td>
                                <td>
                                    @if ($agendamento->status === 'pago')
                                        <span class="badge badge-primary">Pago</span>
                                    @elseif ($agendamento->status === 'confirmado')
                                        <span class="badge badge-info">Confirmado</span>
                                    @elseif ($agendamento->status === 'realizado')
                                        <span class="badge badge-success">Realizado</span>
                                    @elseif ($agendamento->status === 'cancelado')
                                        <span class="badge badge-danger">Cancelado</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($agendamento->status) }}</span>
                                    @endif
                                </td>
                            </tr> {{-- ✅ fechamento do <tr> --}}
                        @endforeach
                    </tbody>

                </table>
            </div>

            <div class="pagination-controls"
                style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                <div class="per-page-selector">
                    <label for="perPage">Itens por página:</label>
                    <select id="perPage" onchange="window.location.href = '?per_page=' + this.value" class="form-control">
                        @foreach ([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>
                                {{ $option }}</option>
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

    {{-- MODAL: Registrar Pagamento --}}
    <div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formPagamento" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPagamentoLabel">Registrar Pagamento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="agendamentoId" name="agendamento_id">

                    <div class="mb-2">
                        <small class="text-muted d-block">Cliente</small>
                        <strong id="mpCliente">—</strong>
                    </div>

                    <div class="mb-2 d-flex flex-wrap" style="gap:1rem;">
                        <div>
                            <small class="text-muted d-block">Serviço</small>
                            <strong id="mpServico">—</strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Horário</small>
                            <strong id="mpHorario">—</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Valor do Serviço</small>
                        <strong id="mpValorServico">R$ —</strong>
                    </div>

                    <div class="form-group">
                        <label for="valor_pago">Valor pago</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="valor_pago"
                            name="valor_pago" required>
                        <small class="form-text text-muted">Informe o valor efetivamente pago pelo cliente.</small>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar pagamento</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ajuste visual para indicar clicável --}}
    <style>
        .clickable-row {
            cursor: pointer;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = $('#modalPagamento');
            const form = document.getElementById('formPagamento');

            function openPagamentoModal(row) {
                document.getElementById('agendamentoId').value = row.dataset.id;
                document.getElementById('mpCliente').innerText = row.dataset.cliente;
                document.getElementById('mpServico').innerText = row.dataset.servico;
                document.getElementById('mpHorario').innerText = row.dataset.horario;
                document.getElementById('mpValorServico').innerText = 'R$ ' + row.dataset.valorServico;

                const inputValorPago = document.getElementById('valor_pago');
                inputValorPago.value = '';
                if (row.dataset.valorPago && !isNaN(row.dataset.valorPago)) {
                    inputValorPago.value = Number(row.dataset.valorPago).toFixed(2);
                }

                // ação vem pronta pela rota nomeada por linha
                form.setAttribute('action', row.dataset.action);

                modal.modal('show');
            }

            document.querySelectorAll('tr.clickable-row').forEach(tr => {
                tr.addEventListener('click', () => openPagamentoModal(tr));
                tr.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        openPagamentoModal(tr);
                    }
                });
            });
        });
    </script>
@endsection
