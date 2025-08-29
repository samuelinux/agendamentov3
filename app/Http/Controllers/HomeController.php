<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;

class HomeController extends Controller
{
    public function index()
    {
        $empresas = Empresa::where('ativo', true)->get();
        
        return view('home', compact('empresas'));
    }

    public function empresa($slug)
    {
        $empresa = Empresa::where('slug', $slug)->where('ativo', true)->firstOrFail();
        $servicos = $empresa->servicos()->ativos()->get();
        
        return view('empresa', compact('empresa', 'servicos'));
    }
}
