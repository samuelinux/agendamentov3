<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use Illuminate\Support\Str;

class EmpresaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->empresa_id !== null) {
                abort(403, 'Acesso negado. Apenas Super Admin pode gerenciar empresas.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $empresas = Empresa::orderBy('nome')->get();
        return view('admin.empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('admin.empresas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tamanho_slot_minutos' => 'required|integer|min:5|max:60',
            'antecedencia_minima_horas' => 'required|integer|min:0|max:72',
        ]);

        $slug = Str::slug($request->nome);
        
        // Verificar se o slug já existe
        $contador = 1;
        $slugOriginal = $slug;
        while (Empresa::where('slug', $slug)->exists()) {
            $slug = $slugOriginal . '-' . $contador;
            $contador++;
        }

        Empresa::create([
            'nome' => $request->nome,
            'slug' => $slug,
            'tamanho_slot_minutos' => $request->tamanho_slot_minutos,
            'antecedencia_minima_horas' => $request->antecedencia_minima_horas,
            'ativo' => true
        ]);

        return redirect()->route('admin.empresas.index')
            ->with('success', 'Empresa criada com sucesso!');
    }

    public function edit(Empresa $empresa)
    {
        return view('admin.empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tamanho_slot_minutos' => 'required|integer|min:5|max:60',
            'antecedencia_minima_horas' => 'required|integer|min:0|max:72',
        ]);

        $slug = Str::slug($request->nome);
        
        // Verificar se o slug já existe (exceto para a empresa atual)
        $contador = 1;
        $slugOriginal = $slug;
        while (Empresa::where('slug', $slug)->where('id', '!=', $empresa->id)->exists()) {
            $slug = $slugOriginal . '-' . $contador;
            $contador++;
        }

        $empresa->update([
            'nome' => $request->nome,
            'slug' => $slug,
            'tamanho_slot_minutos' => $request->tamanho_slot_minutos,
            'antecedencia_minima_horas' => $request->antecedencia_minima_horas,
        ]);

        return redirect()->route('admin.empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    public function toggleStatus(Empresa $empresa)
    {
        $empresa->update(['ativo' => !$empresa->ativo]);
        
        $status = $empresa->ativo ? 'ativada' : 'desativada';
        return redirect()->route('admin.empresas.index')
            ->with('success', "Empresa {$status} com sucesso!");
    }
}
