@extends('admin.layout')

@section('title', 'Configurações do WhatsApp')
@section('header', 'Configurações do WhatsApp - ' . $empresa->nome)

@section('content')
<div class="card">
    <h2>Configurações da API do WhatsApp</h2>
    <p>Configure as credenciais da API oficial do WhatsApp (Meta Cloud API) para envio de mensagens de agendamento e cancelamento.</p>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <form method="POST" action="{{ route('admin.wpp.config.save') }}">
        @csrf
        
        <div class="form-group">
            <label for="phone_number_id">Phone Number ID</label>
            <input 
                type="text" 
                id="phone_number_id" 
                name="phone_number_id" 
                value="{{ old('phone_number_id', $config?->phone_number_id) }}"
                class="form-control"
                placeholder="Ex: 123456789012345"
                required
            >
            @error('phone_number_id')
                <div class="text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="waba_id">WhatsApp Business Account ID (WABA ID)</label>
            <input 
                type="text" 
                id="waba_id" 
                name="waba_id" 
                value="{{ old('waba_id', $config?->waba_id) }}"
                class="form-control"
                placeholder="Ex: 123456789012345"
                required
            >
            @error('waba_id')
                <div class="text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="token">Token de Acesso</label>
            <input 
                type="password" 
                id="token" 
                name="token" 
                value="{{ old('token') }}"
                class="form-control"
                placeholder="Token de acesso da API do WhatsApp"
                required
            >
            @error('token')
                <div class="text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
            <small style="color: #666; font-size: 0.875rem;">
                O token será criptografado e armazenado com segurança.
            </small>
        </div>
        
        <div class="form-group">
            <label for="sender_display_name">Nome de Exibição do Remetente</label>
            <input 
                type="text" 
                id="sender_display_name" 
                name="sender_display_name" 
                value="{{ old('sender_display_name', $config?->sender_display_name) }}"
                class="form-control"
                placeholder="Ex: {{ $empresa->nome }}"
                required
            >
            @error('sender_display_name')
                <div class="text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>
        
        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                Salvar Configurações
            </button>
            
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                Voltar ao Dashboard
            </a>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3>Como obter as credenciais</h3>
    <ol style="margin-left: 2rem;">
        <li><strong>Acesse o Meta for Developers:</strong> Vá para <a href="https://developers.facebook.com/" target="_blank">developers.facebook.com</a></li>
        <li><strong>Crie um App:</strong> Crie um novo app ou use um existente</li>
        <li><strong>Adicione o WhatsApp Business:</strong> Adicione o produto "WhatsApp Business" ao seu app</li>
        <li><strong>Configure o número:</strong> Configure um número de telefone para envio de mensagens</li>
        <li><strong>Obtenha as credenciais:</strong>
            <ul style="margin-left: 1rem;">
                <li><strong>Phone Number ID:</strong> Encontrado na seção "Phone Numbers" do WhatsApp Business</li>
                <li><strong>WABA ID:</strong> ID da conta comercial do WhatsApp</li>
                <li><strong>Token:</strong> Token de acesso temporário ou permanente</li>
            </ul>
        </li>
    </ol>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3>Status da Configuração</h3>
    @if($config)
        <div style="color: #27ae60;">
            ✅ <strong>Configurado:</strong> As credenciais do WhatsApp estão configuradas.
        </div>
        <div style="margin-top: 1rem;">
            <strong>Phone Number ID:</strong> {{ $config->phone_number_id }}<br>
            <strong>WABA ID:</strong> {{ $config->waba_id }}<br>
            <strong>Nome de Exibição:</strong> {{ $config->sender_display_name }}<br>
            <strong>Token:</strong> ••••••••••••••••
        </div>
    @else
        <div style="color: #e74c3c;">
            ❌ <strong>Não Configurado:</strong> Configure as credenciais do WhatsApp para começar a enviar mensagens.
        </div>
    @endif
</div>
@endsection

