@extends('layout')

@section('title', 'Cancelar Agendamento')
@section('header', 'Cancelar Agendamento')
@section('subtitle', 'Informe os dados do seu agendamento')

@section('content')
<a href="{{ route('home') }}" class="back-link">
    ← Voltar para início
</a>

<div style="background: #fff5f5; border: 2px solid #fed7d7; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
    <h4 style="margin: 0 0 1rem 0; color: #742a2a;">⚠️ Atenção</h4>
    <p style="margin: 0 0 0.5rem 0; color: #742a2a;">
        O cancelamento é <strong>irreversível</strong>. Após cancelar, você precisará fazer um novo agendamento.
    </p>
    <p style="margin: 0; color: #742a2a;">
        Certifique-se de que realmente deseja cancelar antes de prosseguir.
    </p>
</div>

<form method="POST" action="{{ route('agendamento.cancelar') }}">
    @csrf
    
    <div class="form-group">
        <label for="agendamento_id">Código do Agendamento:</label>
        <input type="number" id="agendamento_id" name="agendamento_id" class="form-control" 
               placeholder="Digite o código (ex: 123)" required value="{{ old('agendamento_id') }}">
        <small style="color: #718096; font-size: 0.9rem;">
            O código foi informado na confirmação do agendamento (ex: #123)
        </small>
    </div>
    
    <div class="form-group">
        <label for="telefone">Seu Telefone:</label>
        <input type="tel" id="telefone" name="telefone" class="form-control" 
               placeholder="(11) 99999-9999" required value="{{ old('telefone') }}">
        <small style="color: #718096; font-size: 0.9rem;">
            O mesmo telefone usado no agendamento
        </small>
    </div>
    
    <div style="margin-top: 2rem;">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja cancelar este agendamento? Esta ação não pode ser desfeita.')">
            Cancelar Agendamento
        </button>
        
        <a href="{{ route('home') }}" class="btn btn-secondary">
            Voltar sem Cancelar
        </a>
    </div>
</form>

<div style="margin-top: 2rem; padding: 1.5rem; background: #f7fafc; border-radius: 12px;">
    <h4 style="margin: 0 0 1rem 0; color: #2d3748;">💡 Dicas</h4>
    <ul style="margin: 0; padding-left: 1.5rem; color: #4a5568;">
        <li style="margin-bottom: 0.5rem;">O código do agendamento foi enviado via WhatsApp quando você confirmou</li>
        <li style="margin-bottom: 0.5rem;">Use o mesmo telefone que foi cadastrado no agendamento</li>
        <li style="margin-bottom: 0.5rem;">Não é possível cancelar agendamentos que já passaram</li>
        <li>Se tiver dúvidas, entre em contato diretamente com a empresa</li>
    </ul>
</div>
@endsection

@section('scripts')
<script>
// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
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

