<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\ServicoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rotas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rotas de agendamento
Route::get('/cancelar-agendamento', [AgendamentoController::class, 'mostrarCancelamento'])->name('agendamento.cancelar');
Route::post('/cancelar-agendamento', [AgendamentoController::class, 'cancelarAgendamento']);

// Rotas de administração
Route::prefix('admin')->name('admin.')->group(function () {
    // Rotas de autenticação
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rotas protegidas por autenticação
    Route::middleware('auth')->group(function () {
        Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        
        // Rotas para Super Admin (gestão de empresas)
        Route::resource('empresas', EmpresaController::class)->except(['show', 'destroy']);
        Route::patch('empresas/{empresa}/toggle-status', [EmpresaController::class, 'toggleStatus'])->name('empresas.toggle-status');
        
        // Rotas para Admin da Empresa (gestão de serviços)
        Route::resource('servicos', ServicoController::class)->except(['show']);
        Route::patch('servicos/{servico}/toggle-status', [ServicoController::class, 'toggleStatus'])->name('servicos.toggle-status');
    });
});

// Rotas da empresa (devem ficar por último para não conflitar)
Route::get('/{empresa:slug}', [HomeController::class, 'empresa'])->name('empresa');
Route::get('/{empresa:slug}/{servico}/agendar', [AgendamentoController::class, 'mostrarHorarios'])->name('agendamento.horarios');
Route::post('/{empresa:slug}/{servico}/agendar', [AgendamentoController::class, 'confirmarAgendamento'])->name('agendamento.confirmar');
