<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;

class EmpresaConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            // Verificar se é admin da empresa
            if ($user->empresa_id === null) {
                abort(403, 'Acesso negado. Esta área é apenas para administradores de empresa.');
            }
            
            return $next($request);
        });
    }

    public function edit()
    {
        $empresa = auth()->user()->empresa;
        return view('admin.empresa-config.edit', compact('empresa'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'tamanho_slot_minutos' => 'required|integer|min:5|max:60',
            'antecedencia_minima_horas' => 'required|integer|min:0|max:72',
        ]);

        $empresa = auth()->user()->empresa;
        
        $empresa->update([
            'tamanho_slot_minutos' => $request->tamanho_slot_minutos,
            'antecedencia_minima_horas' => $request->antecedencia_minima_horas,
        ]);

        return redirect()->route('admin.empresa-config.edit')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}

