@extends('layout')

@section('title', 'Área do Cliente')
@section('header', 'Área do Cliente')
@section('subtitle', 'Acesse seus agendamentos')

@section('content')

<form method="POST" action="{{ route('cliente.area') }}">
    @csrf
    
    <div class="form-group">
        <label for="telefone_cliente">Telefone (WhatsApp):</label>
        <input type="tel" id="telefone_cliente" name="telefone" class="form-control" maxlength="15"
               placeholder="(11) 99999-9999" value="{{ old('telefone') }}" required>
        <small style="color: #718096; font-size: 0.85rem; margin-top: 0.5rem; display: block;">
            Digite o mesmo telefone usado nos agendamentos
        </small>
    </div>
    
    <button type="submit" class="btn btn-primary">
        Acessar Meus Agendamentos
    </button>
    
    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">
        <a href="{{ url('/') }}" class="btn btn-secondary">
            ← Voltar para início
        </a>
    </div>
</form>

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

