<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Tentar autenticar apenas usuários do tipo admin
        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->tipo === 'admin') {
                $request->session()->regenerate();
                
                // Redirecionar baseado no tipo de admin
                if ($user->empresa_id === null) {
                    // Super Admin
                    return redirect()->route('admin.empresas.index');
                } else {
                    // Admin da Empresa
                    return redirect()->route('admin.dashboard');
                }
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Acesso negado. Apenas administradores podem acessar.',
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        if ($user->empresa_id === null) {
            // Super Admin - redirecionar para gestão de empresas
            return redirect()->route('admin.empresas.index');
        }
        
        // Admin da Empresa - mostrar dashboard
        $empresa = $user->empresa;
        $agendamentosHoje = $empresa->agendamentos()
            ->whereDate('data_hora_inicio', today())
            ->confirmados()
            ->count();
            
        return view('admin.dashboard', compact('empresa', 'agendamentosHoje'));
    }
}
