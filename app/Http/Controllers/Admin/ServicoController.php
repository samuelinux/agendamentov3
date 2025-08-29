<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servico;

class ServicoController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->empresa_id === null) {
                abort(403, 'Acesso negado. Super Admin não pode gerenciar serviços.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $servicos = auth()->user()->empresa->servicos()->orderBy('nome')->get();
        return view('admin.servicos.index', compact('servicos'));
    }

    public function create()
    {
        return view('admin.servicos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao_minutos' => 'required|integer|min:5|max:480',
            'valor' => 'required|numeric|min:0|max:9999.99',
        ]);

        auth()->user()->empresa->servicos()->create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'duracao_minutos' => $request->duracao_minutos,
            'valor' => $request->valor,
            'ativo' => true
        ]);

        return redirect()->route('admin.servicos.index')
            ->with('success', 'Serviço criado com sucesso!');
    }

    public function edit(Servico $servico)
    {
        // Verificar se o serviço pertence à empresa do usuário
        if ($servico->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acesso negado.');
        }

        return view('admin.servicos.edit', compact('servico'));
    }

    public function update(Request $request, Servico $servico)
    {
        // Verificar se o serviço pertence à empresa do usuário
        if ($servico->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acesso negado.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao_minutos' => 'required|integer|min:5|max:480',
            'valor' => 'required|numeric|min:0|max:9999.99',
        ]);

        $servico->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'duracao_minutos' => $request->duracao_minutos,
            'valor' => $request->valor,
        ]);

        return redirect()->route('admin.servicos.index')
            ->with('success', 'Serviço atualizado com sucesso!');
    }

    public function toggleStatus(Servico $servico)
    {
        // Verificar se o serviço pertence à empresa do usuário
        if ($servico->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acesso negado.');
        }

        $servico->update(['ativo' => !$servico->ativo]);
        
        $status = $servico->ativo ? 'ativado' : 'desativado';
        return redirect()->route('admin.servicos.index')
            ->with('success', "Serviço {$status} com sucesso!");
    }

    public function destroy(Servico $servico)
    {
        // Verificar se o serviço pertence à empresa do usuário
        if ($servico->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acesso negado.');
        }

        // Verificar se há agendamentos para este serviço
        if ($servico->agendamentos()->confirmados()->exists()) {
            return redirect()->route('admin.servicos.index')
                ->with('error', 'Não é possível excluir um serviço que possui agendamentos confirmados.');
        }

        $servico->delete();

        return redirect()->route('admin.servicos.index')
            ->with('success', 'Serviço excluído com sucesso!');
    }
}
