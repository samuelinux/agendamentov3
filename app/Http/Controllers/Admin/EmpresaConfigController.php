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

            // Somente Admin de Empresa (empresa_id != null)
            if ($user->empresa_id === null) {
                abort(403, 'Acesso negado. Esta área é apenas para administradores de empresa.');
            }

            return $next($request);
        });
    }

    /**
     * Exibe a tela de configurações com opções prontas para os selects/inputs.
     */
    public function edit()
    {
        $empresa = auth()->user()->empresa;

        // Opções permitidas no front (alinhadas à validação do update)
        $slotOptions         = [5, 10, 15, 20, 30, 60];
        $antecedenciaOptions = [0, 1, 2, 4, 12, 24];

        // Limites do campo numérico (usado na view)
        $limiteDiasMin = 1;
        $limiteDiasMax = 999;

        return view('admin.empresa-config.edit', compact(
            'empresa',
            'slotOptions',
            'antecedenciaOptions',
            'limiteDiasMin',
            'limiteDiasMax'
        ));
    }

    /**
     * Persistência das configurações com validação forte no back-end.
     */
    public function update(Request $request)
    {
        $request->validate([
            // Restringe às opções apresentadas no select (evita valores “fora da curva”)
            'tamanho_slot_minutos'      => ['required', 'integer', 'in:5,10,15,20,30,60'],
            'antecedencia_minima_horas' => ['required', 'integer', 'in:0,1,2,4,12,24'],

            // Novo campo: inteiro de 1 a 999
            'limite_dias_agenda'        => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $empresa = auth()->user()->empresa;

        $empresa->update([
            'tamanho_slot_minutos'      => $request->integer('tamanho_slot_minutos'),
            'antecedencia_minima_horas' => $request->integer('antecedencia_minima_horas'),
            'limite_dias_agenda'        => $request->integer('limite_dias_agenda'),
        ]);

        return redirect()
            ->route('admin.empresa-config.edit')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}
